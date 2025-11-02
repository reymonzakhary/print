<template>
	<h3
		class="
			mb-2
			text-xs
			font-bold
			tracking-wide
			uppercase
			text-themecontrast-400
		"
	>
		<font-awesome-icon :icon="icon" />
		<slot />
	</h3>
</template>

<script>
export default {
	name: "ActionBarSectionTitle",
	props: {
		icon: {
			type: Array,
			default: () => ["fal", "window-restore"],
		},
	},
};
</script>