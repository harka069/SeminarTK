// navigation.js

const Navigation = (() => {
  // Redirect to a given page
  function redirectTo(path) {
    window.location.href = path;
  }

  // Check if user is authenticated (simple example)
  function isAuthenticated() {
    // Example: check if token exists in localStorage
    return !!localStorage.getItem("access_token");
  }

  // Redirect unauthenticated users to login page
  function protectPage() {
    if (!isAuthenticated()) {
      redirectTo("/frontend/meni.html");
    }
  }

  // Expose functions if needed
  return {
    redirectTo,
    isAuthenticated,
    protectPage
  };
})();
