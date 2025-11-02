<template>
    <button
        class="flex items-center justify-between w-full px-2 py-1 text-left transition-colors duration-75 group hover:bg-gray-200 dark:hover:bg-black"
        :class="classes"
    >
		<span
            class="flex items-center justify-between w-full"
            v-tooltip="item.description"
        >
			<template v-for="media in item.media">
				<span
                    :key="media.name"
                    v-if="media.path"
                    class="w-6 h-6 overflow-hidden bg-blue-500 rounded-full"
                >
					<img :src="media.path" :alt="media.name"/>
				</span>
			</template>

			<span class="flex items-center" v-if="item.object">
				<font-awesome-icon
                    :icon="['fal', 'exclamation-triangle']"
                    class="mr-2 text-sm"
                />
				<p>{{ item.object.name }}</p>
			</span>
			<p v-else>{{ item.name }}</p>
		</span>

        <font-awesome-icon
            v-show="$parent.loading === item.slug"
            :icon="['fad', 'spinner-third']"
            class="text-blue-500 fa-spin"
        />

        <span
            v-if="item.matches && item.matches.length > 0"
            @click="$parent.matchButtonClicked(item)"
            class="flex-none px-2 py-1 text-xs text-white bg-yellow-400 rounded-full hover:bg-yellow-500"
        >
			<font-awesome-icon :icon="['fal', 'exclamation-triangle']"/>
		</span>

        <font-awesome-layers v-if="item.published === false">
            <font-awesome-icon
                :icon="['fad', 'heart-rate']"
                class="text-pink-500"
            />
            <font-awesome-icon :icon="['fal', 'ban']" class="text-pink-500" transform="grow-10 right-3"/>
        </font-awesome-layers>

        <ItemMenu
            v-if="menuItems"
            class="z-10 invisible group-hover:visible"
            :menuItems="menuItems"
            menuIcon="ellipsis-h"
            menuClass="z-20"
            @item-clicked="$parent.menuItemClicked($event, item, item.slug)"
        />

    </button>
</template>

<script>
export default {
    props: {
        item: Object,
        classes: String,
        type: String,
        menuItems: Array
    },
    data() {
        return {
            tooltip: false
        };
    }
};
</script>

<style>
</style>
