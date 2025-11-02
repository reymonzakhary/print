<template>
    <div>
        <section class="flex p-2">
			<span class="w-1/4 m-2">
				<p class="mb-2 text-sm font-bold tracking-wide uppercase">
					User statistics
				</p>
				<div class="p-4 bg-white rounded shadow dark:bg-gray-800">
					<StackedUsersChart></StackedUsersChart>
				</div>
			</span>

            <span class="w-2/4 m-2">
				<p class="mb-2 text-sm font-bold tracking-wide uppercase">
					Standardization
				</p>
				<div
                    class="flex items-center justify-between p-4 text-center bg-white rounded shadow dark:bg-gray-800"
                >
					<div v-if="loading" class="w-1/2 text-center">
						loading
						<font-awesome-icon :icon="['fal', 'spinner-third']" spin/>
					</div>
                    <!-- <StackedcategoriesChart class="w-1/2"></StackedcategoriesChart> -->
					<div class="w-1/2">Coming soon...</div>
					<StackedcategoriesChart></StackedcategoriesChart>
				</div>
			</span>
        </section>
    </div>
</template>

<script>
import StackedUsersChart from "../Components/dashboard/StackedUsersChart.vue";
import StackedcategoriesChart from "../Components/dashboard/StackedcategoriesChart.vue";
import Authenticated from "../Layouts/Authenticated";

export default {
    layout: Authenticated,
    components: {StackedUsersChart, StackedcategoriesChart},
    data() {
        return {
            loading: true,
        };
    },
};
</script>

<style>
</style>
