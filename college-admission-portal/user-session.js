(function () {
  const USER_ENDPOINT = "../backend/current_user.php";
  const ACTIVE_KEY = "sessionStarted";

  async function fetchCurrentSession() {
    try {
      const response = await fetch(USER_ENDPOINT, {
        credentials: "same-origin",
        headers: { "Cache-Control": "no-cache" }
      });

      if (!response.ok) {
        return null;
      }

      const data = await response.json();
      return data && data.success && data.session_started ? true : null;
    } catch (error) {
      console.warn("[user-session] Unable to fetch current session:", error);
      return null;
    }
  }

  function clearApplicantStorage() {
    try {
      localStorage.clear();
    } catch (error) {
      console.warn("[user-session] Unable to clear localStorage:", error);
    }
  }

  async function syncUserStorage() {
    const sessionStarted = await fetchCurrentSession();
    if (!sessionStarted) {
      return;
    }

    const storedSession = localStorage.getItem(ACTIVE_KEY);
    if (storedSession && storedSession !== "true") {
      clearApplicantStorage();
    }

    localStorage.setItem(ACTIVE_KEY, "true");
  }

  document.addEventListener("DOMContentLoaded", syncUserStorage);
})();

