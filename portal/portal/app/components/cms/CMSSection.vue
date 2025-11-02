<template>
	<UICard class="shadow-none !bg-transparent">
		<UICardHeader class="!px-0 -mb-1" :backgroundColor="false">
			<template #left>
				<UICardHeaderTitle :title="title" />
			</template>
		</UICardHeader>
		<slot />
	</UICard>
</template>

<script>
export default {
	name: "CMSSection",
	props: {
		title: {
			type: String,
			default: "Title",
		},
	},
};
</script>