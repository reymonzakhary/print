export const useAuthRepository = () => {
  /**
   * Function to fetch access token
   * @param {string} email
   * @param {string} password
   * @returns {Promise<string>}
   */
  async function fetchToken(email, password) {
    const { $api } = useNuxtApp();
    return $api("/auth/login", { method: "POST", body: { email, password } });
  }

  /**
   * Function to refresh the access token
   * @param {string} refresh_token
   * @returns {Promise<string>}
   */
  async function refreshToken(token) {
    const { $api } = useNuxtApp();
    return $api("/auth/refresh-token", {
      method: "POST",
      body: { refresh_token: token },
    });
  }

  /**
   * Function to invalidate token
   * @returns {Promise<void>}
   */
  async function invalidateToken() {
    const { $api } = useNuxtApp();
    return $api("/account/logout", { method: "POST" });
  }

  /**
   * Function to request forget password
   * @param {string} email
   * @returns {Promise<void>}
   */
  async function requestForgetPassword(email) {
    const { $api } = useNuxtApp();
    const formattedEmail = email.trim().toLowerCase();
    return $api("/auth/reset/initiate", { method: "POST", body: { email: formattedEmail } });
  }

  /**
   * Function to check verification code
   * @param {string} email
   * @param {string} code
   * @returns {Promise<void>}
   */
  async function checkVerificationCode(email, code) {
    const { $api } = useNuxtApp();
    return $api("/auth/reset/verify", { method: "POST", body: { email, token: code } });
  }

  /**
   * Function to reset password
   * @param {string} email
   * @param {string} password
   * @param {string} passwordConfirmation
   * @returns {Promise<void>}
   */
  async function resetPassword(email, password, passwordConfirmation) {
    const { $api } = useNuxtApp();
    return $api("/auth/reset/change", {
      method: "POST",
      body: {
        email,
        password,
        password_confirmation: passwordConfirmation,
      },
    });
  }

  return {
    fetchToken,
    refreshToken,
    invalidateToken,
    requestForgetPassword,
    checkVerificationCode,
    resetPassword,
  };
};
