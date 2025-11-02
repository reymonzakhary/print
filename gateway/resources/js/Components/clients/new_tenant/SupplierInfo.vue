<template>
    <div>
        <div class="relative flex items-center mt-4">
            <div
                class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                :class="[newClient.supplier ? 'bg-blue-500' : 'bg-gray-300']"
            >
                <label
                    for="toggle"
                    class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                    :class="[
						newClient.supplier
							? 'translate-x-6 border-blue-500'
							: 'translate-x-0 border-gray-300',
					]"
                ></label>
                <input
                    type="checkbox"
                    id="toggle"
                    name="toggle"
                    class="w-full h-full appearance-none active:outline-none focus:outline-none"
                    @input="update($event.target.checked)"
                />
            </div>

            Client is a supplier?
        </div>

        <transition name="slide">
            <section v-if="newClient.supplier">
                <div class="relative flex items-center mt-4">
                    <div
                        class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                        :class="[files ? 'bg-blue-500' : 'bg-gray-300']"
                    >
                        <label
                            for="toggle"
                            class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                            :class="[
								files
									? 'translate-x-6 border-blue-500'
									: 'translate-x-0 border-gray-300',
							]"
                        ></label>
                        <input
                            type="checkbox"
                            id="toggle"
                            name="toggle"
                            class="w-full h-full appearance-none active:outline-none focus:outline-none"
                            @change="files = !files"
                        />
                    </div>

                    API files
                </div>

                <div v-if="!files">
                    <div class="text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'plug']" class="mr-1"/>
                        API url:
                    </div>
                    <input
                        type="text"
                        @change="
							update_new_client({
								key: 'apiurl',
								value: $event.target.value,
							})
						"
                        class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    />
                </div>
                <div v-if="files">
                    <div class="text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'file']" class="mr-1"/>
                        API file(s):
                    </div>
                    <input
                        type="file"
                        name=""
                        id=""
                        multiple
                        class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    />
                </div>
            </section>
        </transition>
    </div>
</template>

<script>
import {mapMutations, mapState} from "vuex";

export default {
    data() {
        return {
            files: false,
        };
    },
    computed: {
        ...mapState({
            newClient: (state) => state.clients.newClient,
        }),
    },
    methods: {
        ...mapMutations({
            update_new_client: "clients/update_new_client",
        }),
        update(bool) {
            this.update_new_client({key: "supplier", value: bool});
        },
    },
};
</script>

<style>
</style>
