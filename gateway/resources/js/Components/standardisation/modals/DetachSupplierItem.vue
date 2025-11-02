<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'box-open']"/>
            <font-awesome-icon :icon="['fal', 'unlink']" class="mr-2"/>
            Detach {{ $store.state.standardization.editData.name }}
        </template>

        <template slot="modal-body">
            <section class="flex flex-wrap max-w-xl p-8">
                <p>
                    Are you sure you want to DETACH
                    <i class="capitalize">
                        {{ $store.state.standardization.editData.tenant_name }}'s
                    </i>
                    <b>{{ $store.state.standardization.editData.name }}</b> from
                    <b>{{ item.name }}</b>
                </p>
            </section>
        </template>

        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-yellow-600 rounded-full hover:bg-yellow-800"
                @click="detach()"
            >
                <font-awesome-icon :icon="['fal', 'unlink']"/>
                Detach
            </button>
        </template>
    </confirmation-modal>
</template>

<script>
export default {
    props: {
        item: Object
    },
    data() {
        return {
            new_name: "",
        };
    },
    methods: {
        detach() {
            axios
                .post(
                    `categories/${this.$store.state.standardization.editData.standard}/suppliers/${this.$store.state.standardization.editData.slug}/detach`,
                    {
                        tenant_id: this.$store.state.standardization.editData.tenant
                            .id,
                        type: "suppliers",
                        iso: "EN",
                    }
                )
                .then((response) => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green",
                    });
                    this.closeModal()
                })
                .catch();
        },
        closeModal() {
            this.$parent.closeModal();
        },
    },
};
</script>

<style>
</style>
