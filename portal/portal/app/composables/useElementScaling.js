/**
 * Composable for handling automatic element scaling
 * @param {Object} options Configuration options
 * @param {Ref<HTMLElement>} options.containerRef Reference to the container element
 * @param {Ref<HTMLElement|Component>} [options.headerRef] Optional reference to a header element
 * @param {Number} [options.targetHeight=1122.5] Target height to scale to (default is A4 height)
 * @param {Number} [options.initialDelay=100] Delay before initial scaling
 * @param {Boolean} [options.runTwice=true] Whether to run scale update twice (helps with image loading)
 * @returns {Object} Scaling properties and methods
 */
export default function useElementScaling({
  containerRef,
  headerRef = null,
  targetHeight = 1122.5,
  initialDelay = 200,
  runTwice = true,
}) {
  const scale = ref(1);
  const scaleStyle = computed(() => ({ zoom: scale.value }));
  const initialized = ref(false);

  const updateScale = () => {
    if (!containerRef.value) return;

    // Set the height of the header
    let headerHeight = 0;
    const headRef = headerRef.value;
    headerHeight = headRef && headRef.$el ? headRef.$el.clientHeight : headRef.clientHeight;

    // Set the height of the container
    const contRef = containerRef.value;
    const container = contRef && contRef.$el ? contRef.$el : contRef;
    const contStyle = window.getComputedStyle(container);
    let containerHeight = container.clientHeight;
    containerHeight -= parseFloat(contStyle.paddingTop) + parseFloat(contStyle.paddingBottom);

    // Subtract the height of the header if it exists
    if (!initialized.value && headRef) containerHeight -= headerHeight;

    // Calculate the scale factor
    scale.value = Math.min(containerHeight / targetHeight, 1);

    initialized.value = true;
  };

  const initializeScaling = async () => {
    await nextTick();
    setTimeout(updateScale, initialDelay);
    // Some components (especially with images) need a second calculation
    if (runTwice) setTimeout(updateScale, initialDelay);

    window.addEventListener("resize", updateScale);
  };

  onUnmounted(() => window.removeEventListener("resize", updateScale));

  return {
    scale,
    scaleStyle,
    updateScale,
    initializeScaling,
  };
}
