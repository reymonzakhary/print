<template>
	<div class="" :class="menuClass">
		<button
			class="relative z-10 w-6 h-6 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800"
			@click="open = !open"
			v-click-outside="close"
		>
			<font-awesome-icon :icon="['fal', menuIcon]" class="" />
		</button>
		<transition name="slide" target="div" class="relative z-20">
			<div
				v-if="open"
				class="absolute right-0 block w-48 bg-white border border-gray-300 rounded shadow-lg dark:bg-gray-800 dark:border-black"
				id="dropdown-menu"
				role="menu"
			>
				<template v-for="(item, i) in menuItems">
					<button
						:href="item.href"
						:class="[
							item.classes,
							'w-full text-left py-2 px-3 hover:bg-gray-100 dark:hover:bg-gray-900 border-b dark:border-black',
							{ 'border-none rounded-b': i == menuItems.length - 1 },
						]"
						v-if="item.show"
						:key="item.title"
						@click="click(item.action)"
					>
						<li :class="'fal fa-' + item.icon" />
						{{ item.title }}
					</button>
				</template>
			</div>
		</transition>
	</div>
</template>

<script>
export default {
	props: {
		menuClass: {
			type: String,
			default: "ellipsis",
		},
		menuIcon: {
			type: String,
		},
		menuItems: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			open: false,
		};
	},
	methods: {
		click(item) {
			// console.log(item);

			this.$emit("item-clicked", item);
		},
		close() {
			this.open = false;
		},
	},
	directives: {
		"click-outside": {
			bind: function (el, binding, vNode) {
				// Provided expression must evaluate to a function.
				if (typeof binding.value !== "function") {
					const compName = vNode.context.name;
					let warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`;
					if (compName) {
						warn += `Found in component '${compName}'`;
					}

					console.warn(warn);
				}
				// Define Handler and cache it on the element
				const bubble = binding.modifiers.bubble;
				const handler = (e) => {
					if (bubble || (!el.contains(e.target) && el !== e.target)) {
						binding.value(e);
					}
				};
				el.__vueClickOutside__ = handler;

				// add Event Listeners
				document.addEventListener("click", handler);
			},

			unbind: function (el, binding) {
				// Remove Event Listeners
				document.removeEventListener("click", el.__vueClickOutside__);
				el.__vueClickOutside__ = null;
			},
		},
	},
};
</script>
