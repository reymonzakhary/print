/**
 * Composable for managing a horizontally scrollable menu with scroll buttons.
 *
 * @returns {object} - Object containing refs and functions for menu control.
 * @property {import('vue').Ref<HTMLElement | null>} menuRef - Ref to be attached to the scrollable menu container.
 * @property {import('vue').Ref<boolean>} showLeftScrollButton - Ref indicating if the left scroll button should be shown.
 * @property {import('vue').Ref<boolean>} showRightScrollButton - Ref indicating if the right scroll button should be shown.
 * @property {(direction: 'left' | 'right') => void} scrollMenu - Function to scroll the menu left or right.
 */
export function useScrollableMenu({ scrollThreshold = 50 } = {}) {
  const menuRef = ref(null);
  const showLeftScrollButton = ref(false);
  const showRightScrollButton = ref(false);

  const checkScrollPosition = () => {
    const menu = menuRef.value;
    if (!menu) return;

    const scrollLeft = menu.scrollLeft;
    const scrollWidth = menu.scrollWidth;
    const clientWidth = menu.clientWidth;

    // Show left button if scrollLeft is greater than a small threshold (e.g., 10px)
    showLeftScrollButton.value = scrollLeft > scrollThreshold;

    // Show right button if there's more content to scroll to the right than the threshold
    showRightScrollButton.value = scrollWidth - scrollLeft - clientWidth > scrollThreshold;
  };

  const scrollMenu = (direction) => {
    const menu = menuRef.value;
    if (!menu) return;

    const scrollAmountPercentage = 0.8;
    const scrollDistance = menu.clientWidth * scrollAmountPercentage;
    const buffer = scrollThreshold + 1;

    if (direction === "left") {
      const currentScrollLeft = menu.scrollLeft;
      let targetScroll = -scrollDistance;

      // If scrolling left would leave less than buffer pixels, scroll all the way left
      if (currentScrollLeft <= scrollDistance + buffer) {
        targetScroll = -currentScrollLeft;
      }
      menu.scrollBy({ left: targetScroll, behavior: "smooth" });
    } else {
      // direction === 'right'
      const currentScrollLeft = menu.scrollLeft;
      const maxScrollLeft = menu.scrollWidth - menu.clientWidth; // Calculate the maximum scrollLeft value
      const remainingScroll = maxScrollLeft - currentScrollLeft;
      let targetScrollDistance = scrollDistance;

      // If scrolling right would leave less than buffer pixels, scroll all the way right
      if (remainingScroll <= scrollDistance + buffer) {
        // Scroll TO the maximum scroll position smoothly
        menu.scrollTo({ left: maxScrollLeft, behavior: "smooth" });
      } else {
        // Scroll BY the calculated distance smoothly
        menu.scrollBy({ left: targetScrollDistance, behavior: "smooth" });
      }
    }
  };

  let resizeObserver = null;

  onMounted(() => {
    const menu = menuRef.value;
    if (!menu) return;

    // Initial check
    checkScrollPosition();

    // Listen for scroll events to update button visibility
    menu.addEventListener("scroll", checkScrollPosition, { passive: true });
    // Also listen for scrollend to ensure final state is checked after smooth scroll
    menu.addEventListener("scrollend", checkScrollPosition, { passive: true });

    // Use ResizeObserver to check scroll position when the container size changes
    resizeObserver = new ResizeObserver(() => {
      checkScrollPosition();
    });
    resizeObserver.observe(menu);
  });

  onUnmounted(() => {
    const menu = menuRef.value;
    if (menu) {
      menu.removeEventListener("scroll", checkScrollPosition);
      menu.removeEventListener("scrollend", checkScrollPosition); // Remove scrollend listener too
    }
    if (resizeObserver) {
      resizeObserver.disconnect();
    }
  });

  return {
    menuRef,
    showLeftScrollButton,
    showRightScrollButton,
    scrollMenu,
  };
}
