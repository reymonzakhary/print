export const useAuthStore = defineStore("authToken", () => {
  const _accessToken = useCookie("prindustry:auth");
  const _refreshToken = useCookie("prindustry:refresh");
  const _expiration = useCookie("prindustry:expiration");
  const refreshTokenInterval = ref(null);

  const isAuthenticated = computed(() => !!_accessToken.value);

  function $reset() {
    clearInterval(refreshTokenInterval.value);
    _accessToken.value = null;
    _refreshToken.value = null;
    _expiration.value = null;
  }

  const authRepository = useAuthRepository();
  function setTokens({ accessToken, refreshToken, expiration }) {
    _accessToken.value = accessToken;
    _refreshToken.value = refreshToken;
    _expiration.value = expiration;

    refreshTokenInterval.value = setInterval(async () => {
      const { data: tokens } = await authRepository.refreshToken(_refreshToken.value);
      _accessToken.value = tokens.access_token;
      _refreshToken.value = tokens.refresh_token;
      _expiration.value = tokens.expires_in;
    }, _expiration.value * 1000);
  }

  return {
    // State
    expiration: readonly(_expiration),
    accessToken: readonly(_accessToken),
    refreshToken: readonly(_refreshToken),
    isAuthenticated: readonly(isAuthenticated),

    // Actions
    $reset,
    setTokens,
  };
});
