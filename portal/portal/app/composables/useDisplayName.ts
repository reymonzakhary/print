interface DisplayName {
  iso: string;
  display_name: string;
}

export const useDisplayName = () => {
  const { locale, t: $t } = useI18n();

  const getDisplayName = (displayName: DisplayName[] | string): string => {
    if (typeof displayName === "string") return displayName;
    if (!displayName?.length) return "";

    // Get the current language in ISO format
    const lang = locale.value;

    // Find the display name for the current language
    const localizedName = displayName.find((name) => name.iso === lang)?.display_name;

    // We know displayName has at least one element due to the length check above
    const fallbackName = displayName[0]?.display_name ?? $t("Unknown");
    return localizedName ?? fallbackName;
  };

  return {
    getDisplayName,
  };
};
