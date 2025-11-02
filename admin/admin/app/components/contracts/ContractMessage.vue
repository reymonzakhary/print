<template>
  <div>
    <UICard
      :key="handshake.sender_name"
      class="max-h-full overflow-y-auto px-4 shadow-gray-300 dark:shadow-gray-950"
      :rounded-full="true"
    >
      <div class="relative bg-white dark:bg-gray-700">
        <div class="absolute -right-4 -top-0 bg-gray-50 px-2 text-xs text-gray-500">
          contract {{ handshake.contract.contract_nr }}
        </div>
        <!-- more messages from the same sender -->
        <p v-if="handshake.items?.length > 1" class="text-sm pb-2">
          {{ handshake.items?.length }} {{ "messages" }}
          <UIButton class="mt-4" to="/messages" @click.stop.prevent="">
            open conversation
            <font-awesome-icon :icon="['fal', 'external-link']" class="ml-2" />
          </UIButton>
        </p>

        <hr />

        <header class="mt-4 flex items-center justify-between">
          <h3 class="flex w-full items-center justify-between gap-2 text-sm">
            <div class="flex flex-col">
              <span class="flex text-xs uppercase text-gray-500 dark:text-gray-300">
                reseller
              </span>
              <span class="font-bold">{{ handshake.sender_name }}</span>
            </div>
            <!-- <font-awesome-icon
              v-if="handshake.contract.st.name === 'pending'"
              :icon="['fal', 'hand-holding-hand']"
            /> -->
            <div
              v-if="handshake.contract.st.name === 'pending'"
              class="mt-4 text-blue-500 font-bold"
            >
              wants to be a producer
            </div>
            <font-awesome-icon
              v-if="handshake.contract.st.name === 'accepted'"
              :icon="['fal', 'handshake']"
            />
            <font-awesome-icon
              v-if="handshake.contract.st.name === 'declined'"
              :icon="['fal', 'handshake-slash']"
            />
            <div v-if="handshake.contract.st.name === 'accepted'" class="flex items-center">
              <PrindustryLogo class="size-6 flex-none bg-white text-cyan-600" />
              <span class="font-bold text-xs uppercase text-cyan-600 ml-2"> Prindustry </span>
            </div>
          </h3>
        </header>
      </div>

      <div class="sticky bottom-0 flex w-full justify-end bg-white pb-4 dark:bg-gray-700">
        <!-- <UIButton
          v-if="handshake.contract.st.name === 'accepted'"
          class="mt-4"
          variant="theme"
          @click.stop.prevent.self="handleConfigContract(handshake)"
        >
          <font-awesome-icon :icon="['fal', 'gear']" class="mr-2" />
          Contract configuration
        </UIButton> -->
        <UIButton
          v-if="
            handshake.contract.st.name === 'pending' ||
            handshake.contract.st.name === 'accepted' ||
            handshake.contract.st.name === 'suspended'
          "
          class="mt-4 mr-2"
          variant="inverted-danger"
          @click="handleReply(handshake, 'rejected')"
        >
          Decline
        </UIButton>
        <UIButton
          v-if="
            handshake.contract.st.name === 'pending' ||
            handshake.contract.st.name === 'rejected' ||
            handshake.contract.st.name === 'suspended'
          "
          class="mt-4"
          variant="success"
          @click="handleReply(handshake, 'accepted')"
        >
          Accept
        </UIButton>

        <UIButton
          v-if="handshake.contract.st.name === 'accepted'"
          class="mt-4"
          variant="inverted-warning"
          @click="handleReply(handshake, 'suspended')"
        >
          Suspend
        </UIButton>
      </div>
    </UICard>
  </div>
</template>

<script setup>
const props = defineProps({
  handshake: {
    type: Object,
    required: true,
  },
  me: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["reply", "config-contract"]);

const expandMessages = ref(null);
const expandItem = ref(null);
const expandCard = ref(null);

const handleConfigContract = (handshake) => {
  emit("config-contract", handshake);
};
const handleReply = (handshake, status) => {
  emit("reply", { handshake, status });
};
</script>
