<template>
	<transition-group name="fade">
		<!-- message -->
		<article
			v-for="(message, i) in messages"
			:key="'message' + i"
			class="fixed bottom-0 left-0 right-0 z-50 max-w-md p-2 pr-8 mx-auto mb-4 text-sm text-white rounded shadow-md"
			:style="`margin-bottom: ${message.top + 10}px`"
			:class="`bg-${message.status}-500`"
		>
			<button
				class="absolute top-0 right-0 mt-2 mr-2"
				aria-label="delete"
				@click="deleteMessage(i)"
			>
				<font-awesome-icon :icon="['fad', 'times-circle']" />
			</button>

			<font-awesome-icon
				v-if="message.icon"
				:class="'fal fa-' + message.icon"
				class="mr-10"
			/>
			{{ message.text }}
		</article>
	</transition-group>
</template>

<script>
import { mapMutations, mapState } from "vuex";

export default {
	computed: {
		...mapState({
			messages: (state) => state.notification.messages,
		}),
		messages_length() {
			return this.messages.length;
		},
	},
	watch: {
		messages_length() {
			setTimeout(() => {
				this.deleteMessage();
			}, 5500);
		},
	},
	methods: {
		...mapMutations({
			visibility: "notification/show",
			deleteMessage: "notification/delete",
		}),
		hide() {
			this.visibility(false);
		},
	},
};
</script>

<style scoped>
.notification {
	position: fixed;
	top: 2rem;
	right: 2rem;
	z-index: 99;
}
</style>
