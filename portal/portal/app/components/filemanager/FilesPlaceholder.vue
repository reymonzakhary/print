<template>
   <div class="mt-1">
      <div class="w-full h-8 px-2 py-1 bg-gray-100 border-b rounded-t loading"></div>
      <div class="w-full h-8 px-2 py-1 bg-gray-100 loading"></div>
      <div class="w-full h-8 px-2 py-1 bg-gray-100 border-t rounded-b loading"></div>
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
      background: linear-gradient(133deg, rgba(237,242,247,1) 0%, rgba(226,232,240,1) 50%, rgba(237,242,247,1) 100%);;
      position: absolute;
      top: 0;
      bottom: 0;
      width: 100%;
      height: 100%;
      transform: translateX(0);
      animation: 1s loading-placeholder ease-in-out infinite;
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