export const useSessionRepository = () => {
  const { $api } = useNuxtApp();

  /**
   * Function to fetch user
   * @returns {Promise<{id: number, username: string, email: string}>}
   */
  const fetchSession = async () => {
    return $api("/account/me");
    // return {
    //   id: data.id,
    //   username: `${data.profile.first_name.charAt(0).toUpperCase() + data.profile.first_name.slice(1)} ${data.profile.last_name.charAt(0).toUpperCase() + data.profile.last_name.slice(1)}`,
    //   email: data.email,
    // };
  };

  return {
    fetchSession,
  };
};
