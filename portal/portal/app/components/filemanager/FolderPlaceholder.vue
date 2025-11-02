<template>
   <div class="flex">
      <div class="w-full h-20 mx-1 my-3 bg-gray-100 rounded sm:w-1/2 lg:w-1/3 loading dark:bg-gray-700"></div>
      <div class="w-full h-20 mx-1 my-3 bg-gray-100 rounded sm:w-1/2 lg:w-1/3 loading dark:bg-gray-700"></div>
   </div>
</template>

<script>
export default {

}
</script>

<style lang="scss" scoped>
.loading {
   position: relative;
   overflow: hidden;
   
   &::after {
      content: "";
      display: block;
      background: #e2e8f0;
      background: linear-gradient(133deg, rgba(237,242,247,1) 0%, rgba(226,232,240,1) 50%, rgba(237,242,247,1) 100%);
      position: absolute;
      top: 0;
      bottom: 0;
      width: 100%;
      height: 100%;
      transform: translateX(0);
      animation: 1s loading-placeholder ease-in-out infinite;
   }
}

.dark {
   .loading {
      &::after {
         content: "";
         display: block;
         background: #202122;
         background: linear-gradient(133deg, rgb(42, 43, 44) 0%, rgb(35, 36, 37) 50%, rgb(42, 43, 44) 100%);
         position: absolute;
         top: 0;
         bottom: 0;
         width: 100%;
         height: 100%;
         transform: translateX(0);
         animation: 1s loading-placeholder ease-in-out infinite;
      }
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