<template>
	<UICard class="p-1" :roundedFull="true">
		<UICardHeader :backgroundColor="false">
			<template #left>
				<UICardHeaderTitle :title="title" :icon="['fal', icon]" />
			</template>
		</UICardHeader>

		<div class="h-full p-2 pt-0">
			<slot />
		</div>
	</UICard>
</template>

<script>
export default {
	name: "CMSSectionCard",
	props: {
		title: {
			type: String,
			default: "Title",
		},
		icon: {
			type: String,
			default: "circle",
		},
	},
};
</script>