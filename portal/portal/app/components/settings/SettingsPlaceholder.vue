<template>
	<div
		class="flex flex-col flex-wrap items-center justify-center w-full h-full text-center"
	>
		<p class="text-xl font-bold text-gray-400">
			{{ $t("settngs venue") }}
		</p>

		<div class="flex items-start justify-center my-8">
			<font-awesome-icon
				:icon="['fal', 'clouds']"
				class="m-4 text-gray-300 fa-3x"
			/>
			<font-awesome-icon
				:icon="['fad', 'gear']"
				class="my-4 text-gray-500 fa-5x"
			/>
			<font-awesome-icon
				:icon="['fal', 'clouds']"
				class="my-4 text-gray-300 fa-2x"
			/>
		</div>

		<p class="text-xl font-bold text-gray-400">
			{{ $t("select settings page") }}
		</p>
	</div>
</template>

<script>
export default {};
</script>

<style>
</style>