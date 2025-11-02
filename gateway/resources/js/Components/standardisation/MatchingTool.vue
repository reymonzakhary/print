<template>
    <div>
        <div
            class="flex mb-4 bg-gray-100"
            v-for="(match, i) in matches"
            :key="match.id"
        >
            <div class="w-2/5 p-4 text-sm border-r">
                <p class="capitalize">
                    {{ match.tenant_name }}'s
                    <span class="font-bold">{{ match.name }}</span>
                </p>
                <p
                    class="mt-4 text-xs font-bold tracking-wide text-gray-500 uppercase"
                >
                    context
                </p>

                <span v-if="category">
					<p class="mt-2 font-bold">Category:</p>
					<p>{{ category.name }}</p>
				</span>

                <span v-if="box">
					<p class="mt-2 font-bold">Box:</p>
					<p>{{ box.name }}</p>
				</span>
            </div>
            <div class="flex flex-wrap w-3/5 p-4">
                <div class="flex flex-col w-3/6">
                    <p
                        class="text-xs font-bold tracking-wide text-gray-500 uppercase"
                    >
                        {{ match.tenant_name }}
                    </p>
                    <p>
                        {{ match.name }}
                    </p>
                    <p
                        class="mt-4 text-xs font-bold tracking-wide text-blue-500 uppercase"
                    >
                        Prindustry
                    </p>
                    <p>
                        {{ item.name }}
                    </p>
                </div>

                <div class="flex items-center w-3/6">
                    <div class="flex items-stretch">
                        <div class="line1"></div>
                        <div class="line2"></div>
                    </div>

                    <div class="ml-20 -mt-4">
                        <small class="text-xs text-gray-500">similarity</small>
                        <span
                            class="px-2 py-1 font-bold bg-yellow-200 rounded"
                            :class="{
								'bg-green-200': match.percentage > 85
							}"
                        >
							{{
                                Math.round((match.percentage + Number.EPSILON) * 100) /
                                100
                            }}%
						</span>
                    </div>
                    <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-4"/>
                    <button
                        class="w-24 px-2 py-1 font-bold text-blue-500 border border-blue-500 rounded hover:bg-blue-100"
                        @click="
							attach(
								type,
								item.slug,
								match.tenant_id,
								'matches',
								match.slug ? match.slug : match.object.slug,
								category ? category.slug : null,
								box ? box.slug : null
							)
						"
                    >
                        MATCH!
                    </button>
                </div>
                <MatchingSelect
                    :type="type"
                    :items="items"
                    :match="match"
                    :i="i"
                    matchType="matches"
                    class="w-full"
                ></MatchingSelect>
                <MatchingNew
                    :type="type"
                    :items="items"
                    :match="match"
                    :i="i"
                    matchType="matches"
                    class="w-full"
                ></MatchingNew>
            </div>
        </div>

        <div v-if="!matches" class="flex p-4 mb-4 bg-gray-100">
            <div class="w-2/5 p-4 text-sm border-r">
                <p class="capitalize">
                    {{ item.tenant_name }}'s
                    <span class="font-bold">{{ item.name }}</span>
                </p>
                <p
                    class="mt-4 text-xs font-bold tracking-wide text-gray-500 uppercase"
                >
                    context
                </p>
            </div>
            <div class="flex flex-wrap w-3/5 p-4">
                <p class="mt-4 font-bold tracking-wide text-red-500 uppercase">
                    Unmatched!
                </p>

                <MatchingSelect
                    :type="type"
                    :items="items"
                    :match="item"
                    :category="category"
                    :box="box"
                    :i="0"
                    matchType="unmatched"
                    class="w-full"
                ></MatchingSelect>
                <MatchingNew
                    :items="items"
                    :match="item"
                    :category="category"
                    :box="box"
                    :i="0"
                    :type="type"
                    matchType="unmatched"
                    class="w-full"
                ></MatchingNew>
            </div>
        </div>
    </div>
</template>

<script>
import match from "../../mixins/match";
import MatchingSelect from "./inputs/MatchingSelect.vue";

export default {
    components: {MatchingSelect},
    mixins: [match],
    props: {
        item: Object,
        items: Array | Object,
        category: Object,
        box: Object,
        type: String
    },
    data() {
        return {
            percentages: []
        };
    },
    computed: {
        matches: function () {
            if (this.item.matches) {
                return _.orderBy(
                    this.item.matches,
                    "similarity.percentage",
                    "desc"
                );
            }
        }
    },
    methods: {
        close() {
            this.$store.commit("standardization/set_category_unmatched", false);
            this.$store.commit("standardization/set_box_unmatched", false);
            this.$store.commit("standardization/set_option_unmatched", false);
        }
    }
};
</script>

<style>
.line1 {
    width: 60px;
    height: 40px;
    @apply border-b-2 border-gray-500 border-opacity-75;
    -webkit-transform: translateY(-52px) translateX(5px) rotate(45deg);
    position: absolute;
}

.line2 {
    width: 60px;
    height: 40px;
    @apply border-b-2 border-blue-500 border-opacity-75;
    -webkit-transform: translateY(-10px) translateX(-22px) rotate(-45deg);
    position: absolute;
}
</style>
