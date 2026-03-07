/* torecon – Simple session auth for internal area
   Password can be changed here. For production, replace with server-side auth. */
const INTERNAL_PASSWORD = 'torecon2024';

function checkAuth(root) {
  if (!sessionStorage.getItem('torecon_auth')) {
    window.location.href = (root || '../') + 'internal/login.html';
  }
}

function login(password) {
  if (password === INTERNAL_PASSWORD) {
    sessionStorage.setItem('torecon_auth', '1');
    return true;
  }
  return false;
}

function logout(root) {
  sessionStorage.removeItem('torecon_auth');
  window.location.href = (root || '../') + 'internal/login.html';
}
