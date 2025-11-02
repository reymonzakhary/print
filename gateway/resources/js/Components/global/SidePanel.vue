<template>
	<section>
		<div
			class="fixed top-0 left-0 z-40 w-screen h-screen bg-black opacity-50"
			@click="$parent.close()"
		></div>
		<transition name="slideleftlarge" class="overflow-hidden">
			<article
				v-if="show"
				class="absolute right-0 z-50 h-full overflow-auto bg-white rounded-tl shadow-md dark:bg-gray-800 top-8"
				:class="classes"
				style="max-height: calc(100vh - 4rem)"
			>
				<!-- header -->
				<header>
					<!-- dynamic header -->
					<slot name="side-panel-header"></slot>

					<button
						class="absolute top-0 right-0 flex items-center justify-center mt-3 mr-3 hover:text-blue-500"
						aria-label="close"
						@click="$parent.close()"
					>
						<font-awesome-icon :icon="['fad', 'times-circle']" />
					</button>
				</header>

				<section>
					<!-- dynamic content ... -->
					<slot name="side-panel-content"></slot>
				</section>

				<!-- footer -->
				<footer>
					<!-- dynamic footer -->
					<slot name="side-panel-footer"></slot>
				</footer>
			</article>
		</transition>
	</section>
</template>

<script>
export default {
	props: {
		classes: {
			type: String,
			default: "w-1/2",
		},
	},
	data() {
		return {
			show: false,
		};
	},
	mounted() {
		this.show = true;
	},
};
</script>

<style>
</style>
