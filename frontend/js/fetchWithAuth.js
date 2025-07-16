
const refreshToken = localStorage.getItem('refresh_token');
const accessToken = localStorage.getItem('access_token');
export async function fetchWithAuth(url, options = {}, retry = true) {
  if (!options.headers) options.headers = {};
  options.headers['Authorization'] = `Bearer ${accessToken}`;

  let response = await fetch(url, options);

  if (response.status === 401 && retry) {
    // Access token might be expired – try refreshing it
    const refreshed = await tryRefreshToken();
    if (refreshed) {
      // Retry the original request with the new access token
      options.headers['Authorization'] = `Bearer ${accessToken}`;
      return fetch(url, options);
    } else {
      // Refresh failed, log out
      handleLogout();
      throw new Error("Unauthorized: Token refresh failed");
    }
  }

  return response;
}
async function tryRefreshToken() {
  try {
    const response = await fetch('http://localhost/avtogvisn/api/refresh', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ refresh_token: refreshToken })
    });

    if (!response.ok) return false;

    const data = await response.json();
    accessToken = data.access_token;
    localStorage.setItem('accessToken', accessToken);
    return true;
  } catch (err) {
    console.error('Refresh token request failed', err);
    return false;
  }
}
