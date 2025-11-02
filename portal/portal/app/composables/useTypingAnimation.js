// useTypingAnimation.js
export function useTypingAnimation({
  baseText = "",
  texts = [],
  typingInterval = 240,
  dotCount = 3,
} = {}) {
  // This ref holds the animated text that your component can use.
  const animatedText = ref(baseText);

  // Internal state for the animation.
  const state = reactive({
    textIndex: 0, // Which text in the array are we animating?
    charIndex: 0, // How many characters have been typed?
    dots: 0, // How many dots have been appended?
    isDeleting: false, // Whether we are in the deletion phase.
  });

  // The function that performs one step of the animation.
  const animate = () => {
    const currentText = texts[state.textIndex];

    if (state.isDeleting) {
      // Deletion phase: first remove the dots.
      if (state.dots > 0) {
        state.dots--;
        animatedText.value = baseText + currentText + ".".repeat(state.dots);
      } else {
        // Then delete characters one by one.
        const newText = currentText.slice(0, state.charIndex);
        animatedText.value = baseText + newText;
        state.charIndex--;

        // Once deletion is complete, move on to the next text.
        if (state.charIndex < 0) {
          state.isDeleting = false;
          state.charIndex = 0;
          state.textIndex = (state.textIndex + 1) % texts.length;
        }
      }
    } else {
      // Typing phase: type out characters.
      if (state.charIndex < currentText.length) {
        state.charIndex++;
        animatedText.value = baseText + currentText.slice(0, state.charIndex);
      } else if (state.dots < dotCount) {
        // Once the text is fully typed, add the dots.
        state.dots++;
        animatedText.value = baseText + currentText + ".".repeat(state.dots);
      } else {
        // After dots, start the deletion phase.
        state.isDeleting = true;
      }
    }
  };

  // Start the animation loop when the component mounts.
  let intervalId = null;
  onMounted(() => {
    intervalId = setInterval(animate, typingInterval);
  });

  // Clear the interval when the component is unmounted.
  onUnmounted(() => {
    clearInterval(intervalId);
  });

  // Expose the animated text (and optionally control functions) to the component.
  return { animatedText };
}
