<template>
  <div>
    <AddMediaSourceHeader
      :step="2"
      to_component="NewMediaSourceName"
      class="sticky top-0 bg-gray-100 dark:bg-gray-800"
    />
    <ConfirmationModal v-if="showDisks">
      <template #modal-header></template>
      <template #modal-body>
        <MediaSourceDiskList />
      </template>
    </ConfirmationModal>
  </div>
</template>

<script>
import AddMediaSourceHeader from "~/components/mediasources/AddMediaSourceHeader";
import MediaSourceDiskList from "~/components/mediasources/MediaSourceDiskList.vue";
import { mapMutations } from "vuex";

export default {
  components: {
    MediaSourceDiskList,
    AddMediaSourceHeader,
  },
  data() {
    return {
      showDisks: false,
      disk: {},
      showFiles: false,
      path: "",
      showUsers: false,
      userId: null,
    };
  },
  methods: {
    ...mapMutations({
      setSelectedMS: "mediasource/setSelectedMS",
      setMSComponent: "mediasource/setMSComponent",
    }),
  },
};
</script>
