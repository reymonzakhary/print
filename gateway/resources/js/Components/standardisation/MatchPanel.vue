<template>
    <div>
        <SidePanel>
            <template slot="side-panel-header">
                <div class="p-4 mt-4">
                    <p class="mb-2 text-lg font-bold tracking-wide uppercase">
                        {{ type }}
                    </p>
                    <div class="flex justify-between">
                        <!-- <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            External value
                        </p> -->
                        <!-- <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            Prindustry value
                        </p> -->
                    </div>

                    <div class="flex justify-between">
                        <p class="mb-2 text-base font-bold tracking-wide uppercase">
                            {{ unmatchedItem.name }}
                        </p>
                    </div>
                </div>
            </template>

            <template slot="side-panel-content">
                <MatchingTool
                    :type="type"
                    :item="unmatchedItem"
                    :items="items"
                    :category="category"
                    :box="box"
                ></MatchingTool>

                <!-- <div class="p-4 mt-4">
                    <p class="mb-2 font-bold tracking-wide uppercase">Boxes</p>

                    <div class="flex justify-between">
                        <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            External value
                        </p>
                        <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            Prindustry value
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-xs">Papersizes</p>
                        <p
                            class="text-xs font-bold tracking-wide text-yellow-500 uppercase"
                        >
                            Not matched
                        </p>
                    </div>
                </div>

                <MatchingTool :type="type" :object="object"></MatchingTool>

                <div class="p-4 mt-4">
                    <p class="mb-2 font-bold tracking-wide uppercase">Options</p>

                    <div class="flex justify-between">
                        <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            External value
                        </p>
                        <p class="mb-2 text-xs font-bold tracking-wide uppercase">
                            Prindustry value
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-xs">DIN A4</p>
                        <p
                            class="mb-2 text-xs font-bold tracking-wide text-yellow-500 uppercase"
                        >
                            Not matched
                        </p>
                    </div>
                </div>

                <MatchingTool :type="type" :object="object"></MatchingTool> -->
            </template>
        </SidePanel>
    </div>
</template>

<script>
import SidePanel from "../global/SidePanel.vue";
import MatchingTool from "./MatchingTool.vue";

export default {
    props: {
        items: Array | Object,
        unmatchedItem: Object,
        type: "",
        category: Object,
        box: Object
    },
    components: {SidePanel, MatchingTool},
    methods: {
        close() {
            this.$parent.close();
        }
    }
};
</script>

<style>
</style>
