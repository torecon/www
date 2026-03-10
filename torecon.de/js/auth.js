/* torecon – Simple session auth for internal area
   Change username and password here as needed. */
const INTERNAL_USER     = 'thomas';
const INTERNAL_PASSWORD = 'torecon2024';

function checkAuth(root) {
  if (!sessionStorage.getItem('torecon_auth')) {
    window.location.href = (root || '../') + 'internal/login.html';
  }
}

function login(username, password) {
  if (username === INTERNAL_USER && password === INTERNAL_PASSWORD) {
    sessionStorage.setItem('torecon_auth', '1');
    return true;
  }
  return false;
}

function logout(root) {
  sessionStorage.removeItem('torecon_auth');
  window.location.href = (root || '../') + 'internal/login.html';
}
