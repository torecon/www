#!/usr/bin/env python3
"""FTP deploy script – uploads local site folders to the correct remote paths.
Credentials and remote paths are passed as env vars, never hardcoded.

Required env vars:
  FTP_HOST   – FTP server hostname
  FTP_USER   – FTP username
  FTP_PASS   – FTP password

Optional env vars (override defaults):
  FTP_PORT             – FTP port (default: 21)
  REMOTE_MAIN          – Remote path for torecon.de      (default: /html)
  REMOTE_INTERN        – Remote path for intern.torecon.de
  REMOTE_ANALYTICS     – Remote path for analytics.torecon.de
  REMOTE_DOWNLOADS     – Remote path for downloads.torecon.de
  REMOTE_GETBRAINBITES – Remote path for getbrainbites.ai
  REMOTE_GETTHINKLESS  – Remote path for getthinkless.ai
  REMOTE_MEISTERLICH   – Remote path for meisterlich.torecon.de

Usage:
  FTP_HOST=ftp.torecon.de FTP_USER=user FTP_PASS=secret python3 ftp_deploy.py
  FTP_HOST=... python3 ftp_deploy.py --site main      # only main site
  FTP_HOST=... python3 ftp_deploy.py --site intern     # only intern
"""

import ftplib
import os
import sys
from pathlib import Path

HOST  = os.environ["FTP_HOST"]
USER  = os.environ["FTP_USER"]
PASS  = os.environ["FTP_PASS"]
PORT  = int(os.environ.get("FTP_PORT", "21"))

BASE  = Path(__file__).parent

SITES = {
    "main": {
        "local":  BASE / "torecon.de",
        "remote": os.environ.get("REMOTE_MAIN", "/html"),
    },
    "intern": {
        "local":  BASE / "intern.torecon.de",
        "remote": os.environ.get("REMOTE_INTERN", "/subdomains/intern/html"),
    },
    "analytics": {
        "local":  BASE / "analytics.torecon.de",
        "remote": os.environ.get("REMOTE_ANALYTICS", "/subdomains/analytics/html"),
    },
    "downloads": {
        "local":  BASE / "downloads.torecon.de",
        "remote": os.environ.get("REMOTE_DOWNLOADS", "/subdomains/downloads/html"),
    },
    "getbrainbites": {
        "local":  BASE / "getbrainbites.ai",
        "remote": os.environ.get("REMOTE_GETBRAINBITES", "/getbrainbites.ai"),
    },
    "getthinkless": {
        "local":  BASE / "getthinkless.ai",
        "remote": os.environ.get("REMOTE_GETTHINKLESS", "/getthinkless.ai"),
    },
    "meisterlich": {
        "local":  BASE / "meisterlich.torecon.de",
        "remote": os.environ.get("REMOTE_MEISTERLICH", "/subdomains/meisterlich/html"),
    },
}


def ensure_remote_dir(ftp, remote_path):
    """Create remote directory recursively if it does not exist."""
    parts = [p for p in remote_path.split("/") if p]
    current = ""
    for part in parts:
        current += "/" + part
        try:
            ftp.mkd(current)
        except ftplib.error_perm:
            pass  # already exists


def upload_dir(ftp, local_dir: Path, remote_dir: str):
    """Recursively upload local_dir to remote_dir."""
    ensure_remote_dir(ftp, remote_dir)
    for item in sorted(local_dir.iterdir()):
        remote_path = f"{remote_dir}/{item.name}"
        if item.is_dir():
            print(f"  DIR  {remote_path}")
            upload_dir(ftp, item, remote_path)
        else:
            print(f"  UP   {remote_path}  ({item.stat().st_size} bytes)")
            try:
                with open(item, "rb") as f:
                    ftp.storbinary(f"STOR {remote_path}", f)
            except Exception as e:
                print(f"  [ERR] {remote_path}: {e}")


def main():
    # Determine which sites to deploy
    sites_to_deploy = list(SITES.keys())
    if "--site" in sys.argv:
        idx = sys.argv.index("--site")
        site_arg = sys.argv[idx + 1] if idx + 1 < len(sys.argv) else ""
        if site_arg not in SITES:
            print(f"Unknown site '{site_arg}'. Choose from: {', '.join(SITES)}")
            sys.exit(1)
        sites_to_deploy = [site_arg]

    print(f"Connecting to {HOST}:{PORT} as {USER} ...")
    ftp = ftplib.FTP()
    ftp.connect(HOST, PORT, timeout=30)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    print(f"Connected. {ftp.getwelcome()}\n")

    for name in sites_to_deploy:
        site = SITES[name]
        local  = site["local"]
        remote = site["remote"]

        if not local.exists():
            print(f"[SKIP] {name}: local folder '{local}' not found.\n")
            continue

        # Skip placeholder-only folders (only contain README.md)
        files = list(local.iterdir())
        if len(files) == 1 and files[0].name == "README.md":
            print(f"[SKIP] {name}: only README.md present, nothing to deploy.\n")
            continue

        print(f"--- Deploying {name} ---")
        print(f"    Local:  {local}")
        print(f"    Remote: {remote}\n")
        upload_dir(ftp, local, remote)
        print(f"\n--- {name} done ---\n")

    ftp.quit()
    print("Deployment complete.")


if __name__ == "__main__":
    main()
