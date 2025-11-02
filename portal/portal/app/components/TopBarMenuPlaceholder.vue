<template>
  <div class="my-auto">
    <div
      class="w-full mx-1 bg-gray-200 rounded-full h-9 loading dark:bg-gray-900"
    ></div>
  </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
.loading {
  position: relative;
  overflow: hidden;

  &::after {
    content: "";
    display: block;
    background: linear-gradient(
      90deg,
      rgba(245, 245, 245, 0) 0%,
      rgba(245, 245, 245, 1) 51%,
      rgba(245, 245, 245, 0) 100%
    );
    // background-position: center;
    background-size: cover;
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    transform: translateX(0);
    animation: 1s loading-placeholder ease-in-out infinite;
  }
}

.dark .loading {
  &::after {
    background: linear-gradient(
      90deg,
      rgba(23, 23, 23, 1) 0%,
      rgba(23, 23, 23, 1) 51%,
      rgba(23, 23, 23, 1) 100%
    );
  }
}

@keyframes loading-placeholder {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}
</style>
