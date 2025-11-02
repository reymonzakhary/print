<template>
	<div>
		<!-- <pre>{{ nested_tree }}</pre> -->
		<ol class="pl-3">
			<li
				v-for="(branch, index) in nested_tree"
				:key="index"
				class="select-none draggable"
			>
				<p
					class="max-w-full px-1 truncate transition-colors duration-75 rounded hover:bg-gray-100 dark:hover:bg-black"
					:title="branch.title"
					:class="{ 'bg-theme-100': selected_item.id === branch.id }"
				>
					<span
						class="inline-block w-3"
						:class="
							branch.children.length>0 ? 'cursor-pointer' : ''
						"
						
					>
               <!-- @click.stop.prevent="
							showSubdirectories(
								directory.path,
								directory.props.showSubdirectories
							)
						" -->
						<!-- <font-awesome-icon
							:icon="[
								'fas',
								arrowState(index) ? 'caret-down' : 'caret-right'
							]"
							v-if="branch.children.length>0"
						/> -->
					</span>
					<!-- <font-awesome-icon
						:icon="['fas', arrowState(index) ? 'folder-open' : 'folder']"
						class="mr-1 cursor-move text-theme-500 handle"
					/> -->

					<a
						class="cursor-pointer hover:text-theme-500"
						:class="{
							'text-theme-500':  selected_item.id === branch.id
						}"
					>
						{{ branch.title }}
					</a>
				</p>

				<!-- <transition name="slide">
					<ResourceMenuTree
						v-if="branch.children.length>0"
						:parent-id="branch.id"
					>
					</ResourceMenuTree>
				</transition> -->
			</li>
		</ol>
	</div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
	props: {
		depth: Number
	},
	data() {
		return {
			nested_tree: []
		};
	},
	computed: {
		...mapState({
			tree: state => state.resources.tree,
			selected_item: state => state.resources.selected_item
		})
	},
	mounted() {
		this.nested_tree = this.unflatten(this.tree);
	},
	methods: {
		unflatten(arr) {
			var tree = [],
				mappedArr = {},
				arrElem,
				mappedElem;

			// First map the nodes of the array to an object -> create a hash table.
			for (var i = 0, len = arr.length; i < len; i++) {
				arrElem = arr[i];
				mappedArr[arrElem.id] = arrElem;
				mappedArr[arrElem.id]["children"] = [];
			}

			for (var id in mappedArr) {
				if (mappedArr.hasOwnProperty(id)) {
					mappedElem = mappedArr[id];
					// If the element is not at the root level, add it to its parent array of children.
					if (mappedElem.parent_id) {
						mappedArr[mappedElem["parent_id"]]["children"].push(
							mappedElem
						);
					}
					// If the element is at the root level, add it to first level elements array.
					else {
						tree.push(mappedElem);
					}
				}
			}
			return tree;
		}
	}
};
</script>

<style>
</style>