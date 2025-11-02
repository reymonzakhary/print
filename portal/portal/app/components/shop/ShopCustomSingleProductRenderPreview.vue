<template>
	<div>
		<template v-for="(preview, i) in previews">
			{{ preview }}
		</template>
	</div>
</template>

<script>
export default {
	props: {
		previews: {
			type: Array,
			requiered: true,
		},
	},
};
</script>

<style>
</style>