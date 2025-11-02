<template>
	<div class="flex w-full">
		<div style="height: 45vh" class="w-full mx-2 bg-gray-200 rounded sm:w-1/2 md:w-full lg:w-1/2 xl:w-2/12 loading dark:bg-gray-700"></div>
		<div style="height: 45vh" class="w-full mx-2 bg-gray-200 rounded sm:w-1/2 md:w-full lg:w-1/2 xl:w-2/12 loading dark:bg-gray-700"></div>
		<div style="height: 45vh" class="w-full mx-2 bg-gray-200 rounded sm:w-1/2 md:w-full lg:w-1/2 xl:w-2/12 loading dark:bg-gray-700"></div>
		<div style="height: 45vh" class="w-full mx-2 bg-gray-200 rounded sm:w-1/2 md:w-full lg:w-1/2 xl:w-2/12 loading dark:bg-gray-700"></div>
		<div style="height: 45vh" class="w-full mx-2 bg-gray-200 rounded sm:w-1/2 md:w-full lg:w-1/2 xl:w-2/12 loading dark:bg-gray-700"></div>
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
			background: #e2e8f0;
			background: linear-gradient(
				133deg,
				rgba(237, 242, 247, 1) 0%,
				rgba(226, 232, 240, 1) 50%,
				rgba(237, 242, 247, 1) 100%
			);
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