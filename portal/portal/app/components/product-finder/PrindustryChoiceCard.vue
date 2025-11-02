<template>
  <div
    class="shadow-prindustry-200 h-full w-full cursor-pointer overflow-hidden rounded shadow-md transition-all hover:scale-[1.025] hover:shadow-xl"
    :style="{
      backgroundImage:
        'url(https://images.unsplash.com/photo-1504941319307-771ebfa02a51?q=80&w=1929&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D)',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }"
  >
    <div
      class="relative grid h-full w-full place-items-center bg-gradient-to-b from-prindustry/50 to-prindustry/80 p-6"
    >
      <PrindustryLogo
        class="absolute -left-[55%] z-0 h-[120%] translate-x-1/2 text-white opacity-25"
      />
      <div class="absolute bottom-6 right-6 text-right">
        <h3 class="mb-1 text-xl font-bold text-white">Drukwerkconcurrent</h3>
        <p class="text-sm text-white">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates voluptas distinctio
          nesciunt quas non animi.
        </p>
      </div>
    </div>
  </div>
</template>
