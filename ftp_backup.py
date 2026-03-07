#!/usr/bin/env python3
"""FTP backup script - credentials are passed as env vars, not hardcoded."""
import ftplib
import os

HOST = os.environ["FTP_HOST"]
USER = os.environ["FTP_USER"]
PASS = os.environ["FTP_PASS"]
PORT = int(os.environ.get("FTP_PORT", "21"))
DEST = os.environ.get("FTP_DEST", "torecon_backup")


def mirror(ftp, remote_dir, local_dir):
    os.makedirs(local_dir, exist_ok=True)
    try:
        items = list(ftp.mlsd(remote_dir))
    except ftplib.error_perm:
        try:
            raw = []
            ftp.retrlines(f"LIST {remote_dir}", raw.append)
            items = parse_list(raw)
        except Exception as e:
            print(f"  [WARN] Cannot list {remote_dir}: {e}")
            return

    for name, facts in items:
        if name in (".", ".."):
            continue
        remote_path = f"{remote_dir}/{name}".replace("//", "/")
        local_path = os.path.join(local_dir, name)
        ftype = facts.get("type", "").lower() if isinstance(facts, dict) else facts
        if ftype in ("dir", "cdir", "pdir"):
            print(f"  DIR  {remote_path}")
            mirror(ftp, remote_path, local_path)
        else:
            size = facts.get("size", "?") if isinstance(facts, dict) else "?"
            print(f"  FILE {remote_path}  ({size} bytes)")
            try:
                with open(local_path, "wb") as f:
                    ftp.retrbinary(f"RETR {remote_path}", f.write)
            except Exception as e:
                print(f"  [ERR] {remote_path}: {e}")


def parse_list(lines):
    items = []
    for line in lines:
        parts = line.split(None, 8)
        if len(parts) < 9:
            continue
        name = parts[8]
        ftype = "dir" if line.startswith("d") else "file"
        items.append((name, {"type": ftype}))
    return items


def main():
    print(f"Connecting to {HOST}:{PORT} as {USER} ...")
    ftp = ftplib.FTP()
    ftp.connect(HOST, PORT, timeout=30)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    print(f"Connected. Server: {ftp.getwelcome()}")
    print(f"\nStarting mirror -> {DEST}/\n")
    mirror(ftp, "/", DEST)
    ftp.quit()
    print("\nDone.")


if __name__ == "__main__":
    main()
