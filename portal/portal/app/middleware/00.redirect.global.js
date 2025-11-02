export default defineNuxtRouteMiddleware((to, from) => {
  let toSanitized = to.path;

  const languagePrefixRegex = /^\/[a-z]{2}\//;
  if (languagePrefixRegex.test(toSanitized)) {
    toSanitized = toSanitized.replace(languagePrefixRegex, "/");
  }

  // const managerPrefixRegex = /^\/manager\//;
  // if (managerPrefixRegex.test(toSanitized)) {
  //   toSanitized = toSanitized.replace(managerPrefixRegex, "/");
  // }

  if (toSanitized !== to.path) {
    return window.location.replace(toSanitized);
  }
});
