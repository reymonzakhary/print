<template>
    <!-- modal -->
    <div tabindex="0" @keyup.esc.stop="close()">
        <!-- card wrapper -->
        <div
            class="fixed top-0 left-0 z-50 flex items-center justify-center w-screen h-screen"
        >
            <!-- card component -->
            <div
                class="bg-white rounded-lg shadow-xl dark:bg-gray-800"
                role="dialog"
                aria-labelledby="modalTitle"
                aria-describedby="modalDescription"
            >
                <!-- header -->
                <header
                    class="relative p-2 bg-gray-200 rounded-t-lg dark:bg-gray-900 dark:text-white min-w-min"
                    id="modalTitle"
                >
                    <!-- dynamic header -->
                    <slot name="modal-header"></slot>

                    <button
                        class="absolute top-0 right-0 flex items-center justify-center mt-3 mr-3 transition-colors hover:text-gray-700"
                        aria-label="close"
                        @click="close()"
                    >
                        <font-awesome-icon :icon="['fad', 'times-circle']"/>
                    </button>
                </header>

                <!-- body -->
                <section
                    class="p-4 text-gray-700 dark:text-gray-400"
                    id="modalDescription"
                >
                    <!-- dynamic content ... -->
                    <slot name="modal-body"></slot>
                </section>

                <!-- footer -->
                <footer class="flex items-center justify-end p-2 rounded-b-lg">
                    <slot name="cancel-button">
                        <button
                            class="px-4 py-1 mr-2 text-sm transition-colors bg-gray-300 rounded-full hover:bg-gray-400 dark:bg-gray-900 dark:text-white"
                            @click="close()"
                            aria-label="Close modal"
                        >
                            Cancel
                        </button>
                    </slot>

                    <!-- dynamic confirm button -->
                    <slot name="confirm-button"></slot>
                </footer>
            </div>
        </div>

        <!-- background -->
        <div
            class="fixed top-0 left-0 z-40 w-screen h-screen bg-black opacity-75"
        ></div>
    </div>
</template>

<script>
export default {
    methods: {
        close() {
            this.$parent.closeModal();
        },
    },
    mounted() {
        // document.body.appendChild(this.$el);
        // document.getElementById('app').prependChild(this.$el);
    },
    computed: {
        theme_colors() {
            return this.$store.state.theme.theme_colors;
        },
        themecontrast_colors() {
            return this.$store.state.theme.text_colors;
        },
    },
};
</script>
