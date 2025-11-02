<template>
    <div class="flex h-full" style="max-height: calc(100vh - 5rem)">
        <div class="z-30 w-64 h-full ml-2">
            <div class="flex items-center justify-between pr-4 mb-2">
                <p class="ml-2 text-sm font-bold tracking-wide uppercase">Values</p>
            </div>

            <transition name="list" tag="nav">
                <section class="h-full p-2 overflow-auto text-sm">
                    <ul class="bg-white rounded-md shadow-md group dark:bg-gray-800">
                        <li
                            class="relative px-4"
                            v-for="(value, key, i) in activeOption"
                            :key="i"
                        >
							<span
                                v-if="
									key === 'unit' ||
										key === 'incremenal_by' ||
										key === 'input_type' ||
										key === 'maximum' ||
										key === 'minimum'
								"
                            >
								<div v-if="key !== 'information'" class="flex py-2">
									<div class="w-1/2">{{ key }}:</div>
									<div class="w-1/2 font-bold">{{ value }}</div>
								</div>

                                <!-- <div v-else class="p-2 border rounded">
                                    <b>Original:</b>
                                    <template v-if="value.length > 0">
                                    <div
                                        v-for="(info, keyinfo, idx) in JSON.parse(value)"
                                        :key="'info_'+idx"
                                        class="flex py-1 text-xs"
                                    >
                                        <div class="w-1/2">{{keyinfo}}:</div>
                                        <div class="w-1/2 font-bold">{{info}}</div>
                                    </div>
                                    </template>
                                </div> -->
							</span>
                        </li>
                    </ul>

                    <ul
                        class="bg-white rounded-md shadow-md group dark:bg-gray-800"
                        v-if="
							activeOption.children && activeOption.children.length > 0
						"
                    >
                        Nested options
                        <li
                            class="relative"
                            v-for="(option, i) in activeOption.children"
                            :key="i"
                        >
                            <Button
                                @click.native="selectOption(option, option.slug)"
                                :item="option"
                                :menuItems="menuItems"
                            ></Button>
                        </li>
                    </ul>
                </section>
            </transition>
        </div>
    </div>
</template>


<script>
import {mapState} from "vuex";

export default {
    computed: {
        ...mapState({
            options: state => state.standardization.options,
            unmatched: state => state.standardization.unmatchedOptions,
            activeCategory: state => state.standardization.activeCategory,
            activeBox: state => state.standardization.activeBox,
            activeOption: state => state.standardization.activeOption
        })
    }
};
</script>

<style>
</style>
