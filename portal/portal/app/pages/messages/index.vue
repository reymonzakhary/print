<template>
  <main class="grid h-screen w-full grid-cols-12 gap-4 p-4">
    <aside class="col-span-4 border-r">
      <h1 class="font-bold uppercase tracking-wide">
        <font-awesome-icon class="mr-2" :icon="['fal', 'envelopes-bulk']" />
        {{ $t("inbox") }}
      </h1>
      <div v-if="messages?.length > 0" class="space-y-4">
        <ul role="list" class="max-h-[40vh] divide-y overflow-y-auto">
          <li
            v-for="message in messages"
            :key="message.id"
            class="relative flex cursor-pointer justify-between gap-x-6 rounded p-4 hover:bg-gray-200 dark:hover:bg-gray-900"
            :class="{ 'bg-theme-50 !text-theme-500': activeMessage === message }"
            @click="handleMessageClick(message)"
          >
            <div class="flex min-w-0 gap-x-4">
              <div v-if="message.type === 'contract'" class="flex flex-col items-center gap-x-2">
                <font-awesome-icon
                  v-tooltip="message.type"
                  class="my-auto size-8 flex-none"
                  :icon="['fal', 'file-signature']"
                  aria-hidden="true"
                />
                <UIButton
                  class="ml-2"
                  :to="`manage/tenant-settings/producer-contracts?type=${message?.whoami}`"
                >
                  {{ $t("contract") }}
                  <font-awesome-icon :icon="['fal', 'external-link']" class="ml-1" />
                </UIButton>
              </div>
              <!-- <img
                class="size-12 flex-none rounded-full bg-gray-50"
                :src="message.imageUrl"
                alt=""
              /> -->
              <div class="min-w-0 flex-auto">
                <section class="flex items-center">
                  <p class="shrink-0 text-sm/6 font-semibold">
                    <!-- <a :href="message.href"> -->
                    <!-- <span class="absolute inset-x-0 -top-px bottom-0" /> -->
                    {{ message.sender_name }}
                    <!-- </a> -->
                  </p>
                </section>
                <p v-tooltip="message.subject" class="truncate text-sm/6 font-bold">
                  <span>{{ message.subject }}</span>
                </p>
                <p class="truncate text-sm/6">
                  {{ message.title }}
                </p>
              </div>
            </div>
            <div class="flex shrink-0 items-center gap-x-4">
              <div class="hidden sm:flex sm:flex-col sm:items-end">
                <p class="ml-2 flex truncate text-xs/5 text-gray-500 dark:text-gray-400">
                  <a
                    v-tooltip="message.sender_email"
                    :href="`mailto:${message.sender_email}`"
                    class="relative truncate hover:underline"
                  >
                    {{ message.sender_email }}
                  </a>
                </p>
                <p class="truncate text-sm/6">
                  <font-awesome-icon
                    v-if="message.read"
                    class="text-green-500"
                    :icon="['fas', 'envelope-open-text']"
                    aria-hidden="true"
                  />
                  <font-awesome-icon
                    v-else
                    class="text-gray-400"
                    :icon="['fas', 'envelope']"
                    aria-hidden="true"
                  />
                </p>
                <p
                  v-if="message.created_at"
                  class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400"
                >
                  <time :datetime="message.created_at">
                    {{ new Date(message.created_at).toLocaleDateString() }}
                  </time>
                </p>
              </div>
              <div class="flex">
                <font-awesome-icon
                  class="size-8 flex-none text-gray-400"
                  :icon="['fal', 'chevron-right']"
                  aria-hidden="true"
                />
              </div>
            </div>
          </li>
        </ul>
      </div>
      <p v-else-if="!isLoading" class="my-4 w-full border-b pb-4 italic text-gray-500">
        {{ $t("No messages found.") }}
      </p>
      <p v-else>
        <UIListSkeleton
          :key="'skeleton1'"
          :skeleton-line-height="12"
          :skeleton-line-amount="5"
          class="w-full"
        />
      </p>

      <h1 class="mt-8 font-bold uppercase tracking-wide">
        <font-awesome-icon class="mr-2" :icon="['fal', 'paper-plane-top']" />
        {{ $t("sent") }}
      </h1>
      <div v-if="sentMessages?.length > 0" class="space-y-4">
        <ul role="list" class="max-h-[40vh] divide-y overflow-y-auto">
          <li
            v-for="message in sentMessages"
            :key="message.id"
            class="relative flex cursor-pointer justify-between gap-x-6 rounded p-4 hover:bg-gray-200 dark:hover:bg-gray-900"
            :class="{ 'bg-theme-50 !text-theme-500': activeMessage === message }"
            @click="handleMessageClick(message)"
          >
            <div class="flex min-w-0 gap-x-4">
              <div v-if="message.type === 'contract'" class="flex flex-col items-center gap-x-2">
                <font-awesome-icon
                  v-tooltip="message.type"
                  class="my-auto size-8 flex-none"
                  :icon="['fal', 'file-signature']"
                  aria-hidden="true"
                />
                <UIButton
                  class="ml-2"
                  :to="`manage/tenant-settings/producer-contracts?type=${message?.whoami}`"
                >
                  {{ $t("contract") }}
                  <font-awesome-icon :icon="['fal', 'external-link']" class="ml-1" />
                </UIButton>
              </div>
              <!-- <img
                      class="size-12 flex-none rounded-full bg-gray-50"
                      :src="message.imageUrl"
                      alt=""
                    /> -->
              <div class="min-w-0 flex-auto">
                <section class="flex items-center">
                  <p class="shrink-0 text-sm/6 font-semibold">
                    <!-- <a :href="message.href"> -->
                    <!-- <span class="absolute inset-x-0 -top-px bottom-0" /> -->
                    {{ message.sender_name }}
                    <!-- </a> -->
                  </p>
                </section>
                <p v-tooltip="message.subject" class="truncate text-sm/6 font-bold">
                  <span>{{ message.subject }}</span>
                </p>
                <p class="truncate text-sm/6">
                  {{ message.title }}
                </p>
              </div>
            </div>
            <div class="flex shrink-0 items-center gap-x-4">
              <div class="hidden sm:flex sm:flex-col sm:items-end">
                <p class="ml-2 flex truncate text-xs/5 text-gray-500 dark:text-gray-400">
                  <a
                    v-tooltip="message.sender_email"
                    :href="`mailto:${message.sender_email}`"
                    class="relative truncate hover:underline"
                  >
                    {{ message.sender_email }}
                  </a>
                </p>
                <p class="truncate text-sm/6">
                  <font-awesome-icon
                    v-if="message.read"
                    class="text-green-500"
                    :icon="['fas', 'envelope-open-text']"
                    aria-hidden="true"
                  />
                  <font-awesome-icon
                    v-else
                    class="text-gray-400"
                    :icon="['fas', 'envelope']"
                    aria-hidden="true"
                  />
                </p>
                <p
                  v-if="message.created_at"
                  class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400"
                >
                  <time :datetime="message.created_at">
                    {{ new Date(message.created_at).toLocaleDateString() }}
                  </time>
                </p>
              </div>
              <div class="flex">
                <font-awesome-icon
                  class="size-8 flex-none text-gray-400"
                  :icon="['fal', 'chevron-right']"
                  aria-hidden="true"
                />
              </div>
            </div>
          </li>
        </ul>
      </div>
      <p v-else-if="!isLoading">{{ $t("No messages found.") }}</p>
      <p v-else>
        <UIListSkeleton
          :key="'skeleton1'"
          :skeleton-line-height="12"
          :skeleton-line-amount="5"
          class="w-full"
        />
      </p>
    </aside>
    <UICard v-if="activeMessage" class="col-span-8 overflow-y-auto" rounded-full>
      <UICardHeader class="sticky top-0 z-10 !justify-between">
        <template #left>
          <div class="flex items-center gap-2 dark:text-theme-50">
            <UICardHeaderTitle
              :icon="['fal', 'envelope-open-text']"
              :title="activeMessage.subject"
            />
            {{ $t("from") }}
            <span class="font-bold">{{ activeMessage.sender_name }}</span>
            <span class="text-theme-100">{{ activeMessage.sender_email }}</span>
            {{ $t("to") }}
            <span class="text-theme-100">{{ activeMessage.recipient_email }}</span>
            <date :datetime="activeMessage.created_at" class="">
              {{
                new Date(activeMessage.created_at).toLocaleDateString() +
                " " +
                new Date(activeMessage.created_at).toLocaleTimeString([], {
                  hour: "2-digit",
                  minute: "2-digit",
                })
              }}
            </date>
          </div>
        </template>
        <template #right>
          <div class="w-full text-right dark:text-theme-50">
            <span class="pr-2 font-bold uppercase tracking-wide">
              {{ activeMessage.type }}
            </span>
            <UIButton
              v-if="activeMessage.type === 'contract'"
              class="ml-2"
              :to="`manage/tenant-settings/producer-contracts?type=${activeMessage?.whoami}`"
            >
              {{ $t("open contract") }}
              <font-awesome-icon :icon="['fal', 'external-link']" class="ml-1" />
            </UIButton>
          </div>
        </template>
      </UICardHeader>

      <ul role="list" class="space-y-4 p-4">
        <li
          v-for="(message, index) in activeMessage.items"
          :key="message.id"
          class="relative flex gap-x-4"
        >
          <div
            :class="[
              index === activeMessage.items.length - 1 ? 'h-6' : '-bottom-6',
              'absolute left-0 top-0 flex w-6 justify-center',
            ]"
          >
            <div class="w-px bg-gray-200" />
          </div>

          <!-- CONTRACT -->
          <!-- <section> -->
          <div
            class="relative mt-6 flex size-6 flex-none items-center justify-center bg-white dark:bg-gray-700"
          >
            <div class="size-1.5 rounded-full bg-theme-100 ring-1 ring-theme-300" />
          </div>
          <div
            class="flex-auto rounded-md bg-gray-50 p-3 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:ring-gray-900"
          >
            <div class="flex justify-between gap-x-4">
              <div class="py-0.5 text-xs/5 text-gray-500 dark:text-gray-400">
                <template v-if="message.from === 'sender'">
                  <span class="font-medium text-gray-900 dark:text-theme-50">
                    {{ message.sender_name }}
                  </span>
                  {{ $t("sent") }}
                </template>
                <template v-else>
                  <span class="font-medium text-gray-900 dark:text-theme-50">
                    {{ message.recipient_email }}
                  </span>
                  {{ $t("replied") }}
                </template>
              </div>

              <time
                :datetime="activeMessage.created_at"
                class="text-xs/5 text-gray-500 dark:text-gray-400"
              >
                {{
                  new Date(activeMessage.created_at).toLocaleDateString() ===
                  new Date().toLocaleDateString()
                    ? new Date(activeMessage.created_at).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : new Date(activeMessage.created_at).toLocaleDateString([], {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                      })
                }}
              </time>
            </div>

            <p class="text-sm/6 text-gray-500 dark:text-gray-400">{{ message.body }}</p>

            <UIButton
              v-if="message.from === 'sender' && index === activeMessage.items.length - 1"
              class="mt-3"
            >
              {{ $t("reply") }}
            </UIButton>

            <UIButton
              v-if="message.type === 'contract' && index === activeMessage.items.length - 1"
              :to="`manage/tenant-settings/producer-contracts?type=${message?.whoami}`"
              class="mt-3 flex flex-col !items-start !rounded !px-4 !text-base shadow-md"
            >
              <div class="font-base font-bold text-black dark:text-theme-50">
                <font-awesome-icon :icon="['fal', 'file-signature']" aria-hidden="true" />
                {{ message.type }}
              </div>
              <div class="text-sm/6">
                {{ $t("open contract") }}
                <font-awesome-icon
                  :icon="['fal', 'external-link']"
                  class="ml-1"
                  aria-hidden="true"
                />
              </div>
            </UIButton>
          </div>
          <!-- </section> -->
        </li>
      </ul>

      <!-- New comment form -->
      <!-- <div class="mt-6 flex gap-x-3">
        <img
          src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
          alt=""
          class="size-6 flex-none rounded-full bg-gray-50"
        />
        <form action="#" class="relative flex-auto">
          <div
            class="overflow-hidden rounded-lg pb-12 outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600"
          >
            <label for="comment" class="sr-only">Add your comment</label>
            <textarea
              id="comment"
              rows="2"
              name="comment"
              class="block w-full resize-none bg-transparent px-3 py-1.5 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6"
              placeholder="Add your comment..."
            />
          </div>

          <div class="absolute inset-x-0 bottom-0 flex justify-between py-2 pl-3 pr-2">
            <div class="flex items-center space-x-5">
              <div class="flex items-center">
                <button
                  type="button"
                  class="-m-2.5 flex size-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-400"
                >
                  <PaperClipIcon class="size-5" aria-hidden="true" />
                  <span class="sr-only">Attach a file</span>
                </button>
              </div>
              <div class="flex items-center">
                <Listbox v-model="selected" as="div">
                  <ListboxLabel class="sr-only">Your mood</ListboxLabel>
                  <div class="relative">
                    <ListboxButton
                      class="relative -m-2.5 flex size-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-400"
                    >
                      <span class="flex items-center justify-center">
                        <span v-if="selected.value === null">
                          <FaceSmileIcon class="size-5 shrink-0" aria-hidden="true" />
                          <span class="sr-only">Add your mood</span>
                        </span>
                        <span v-if="!(selected.value === null)">
                          <span
                            :class="[
                              selected.bgColor,
                              'flex size-8 items-center justify-center rounded-full',
                            ]"
                          >
                            <component
                              :is="selected.icon"
                              class="size-5 shrink-0 text-white"
                              aria-hidden="true"
                            />
                          </span>
                          <span class="sr-only">{{ selected.name }}</span>
                        </span>
                      </span>
                    </ListboxButton>

                    <transition
                      leave-active-class="transition ease-in duration-100"
                      leave-from-class="opacity-100"
                      leave-to-class="opacity-0"
                    >
                      <ListboxOptions
                        class="absolute bottom-10 z-10 -ml-6 w-60 rounded-lg bg-white py-3 text-base shadow ring-1 ring-black/5 focus:outline-none sm:ml-auto sm:w-64 sm:text-sm"
                      >
                        <ListboxOption
                          v-for="mood in moods"
                          :key="mood.value"
                          v-slot="{ active }"
                          as="template"
                          :value="mood"
                        >
                          <li
                            :class="[
                              active ? 'bg-gray-100' : 'bg-white',
                              'relative cursor-default select-none px-3 py-2',
                            ]"
                          >
                            <div class="flex items-center">
                              <div
                                :class="[
                                  mood.bgColor,
                                  'flex size-8 items-center justify-center rounded-full',
                                ]"
                              >
                                <component
                                  :is="mood.icon"
                                  :class="[mood.iconColor, 'size-5 shrink-0']"
                                  aria-hidden="true"
                                />
                              </div>
                              <span class="ml-3 block truncate font-medium">{{ mood.name }}</span>
                            </div>
                          </li>
                        </ListboxOption>
                      </ListboxOptions>
                    </transition>
                  </div>
                </Listbox>
              </div>
            </div>
            <button
              type="submit"
              class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
              Comment
            </button>
          </div>
        </form>
      </div> -->
    </UICard>

    <MessageZeroState v-else :message="$t('No message selected')" class="col-span-8" />
  </main>
</template>
<script setup>
const { fetchMessages, updateMessage } = useMessagesRepository();
const { handleError } = useMessageHandler();
// const { theUser: me } = storeToRefs(useAuthStore);

// State variables
const messages = ref([]);
const sentMessages = ref([]);
const activeMessage = ref(null);
const isLoading = ref(true);

// Lifecycle hooks
onMounted(() => {
  // Fetch inbox messages when the component is mounted
  fetchMessages("recipient")
    .then((response) => {
      // messages.value = groupMessagesBySender(response);
      if (response.length > 0) {
        messages.value = buildMessageTree(response);
      }
      if (useRoute().query.selected) {
        const selectedMessage = messages.value.find(
          (message) => message.id == useRoute().query.selected,
        );
        if (selectedMessage && !activeMessage.value) {
          console.log(selectedMessage);
          activeMessage.value = selectedMessage;
        }
      }
      // Fetch sent messages when the component is mounted
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
    });

  fetchMessages("sender")
    .then((response) => {
      if (response.length > 0) {
        sentMessages.value = buildMessageTree(response);
      }
      if (useRoute().query.selected) {
        const selectedMessage = sentMessages.value.find(
          (message) => message.id == useRoute().query.selected,
        );
        if (selectedMessage && !activeMessage.value) {
          console.log(selectedMessage);
          activeMessage.value = selectedMessage;
        }
      }
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
    });
});

// Methods
// const groupMessagesBySender = (messages) => {
//   return messages.reduce((acc, message) => {
//     const existing = acc.find((h) => h.sender_name === message.sender_name);
//     if (existing) {
//       if (!existing.items) {
//         existing.items = [{ ...existing }];
//       }
//       existing.items.push({ ...message });
//     } else {
//       acc.push({ ...message });
//     }
//     return acc;
//   }, []);
// };
// const groupMessagesByRecipient = (messages) => {
//   return messages.reduce((acc, message) => {
//     const existing = acc.find((h) => h.recipient_email !== message.sender_email);
//     if (existing) {
//       if (!existing.items) {
//         existing.items = [{ ...existing }];
//       }
//       existing.items.push({ ...message });
//     } else {
//       acc.push({ ...message });
//     }
//     return acc;
//   }, []);
// };

const buildMessageTree = (messages) => {
  // Create a map for quick lookup of messages by id
  const messageMap = new Map();

  // First pass: Create all nodes and add children array
  messages.forEach((message) => {
    messageMap.set(message.id, {
      ...message,
      items: [message],
    });
  });

  // Second pass: Build the tree structure
  const rootMessages = [];

  messages.forEach((message) => {
    const messageNode = messageMap.get(message.id);

    if (message.parent_id === null || message.parent_id === undefined) {
      // This is a root message (no parent)
      rootMessages.push(messageNode);
    } else {
      // This is a child message - find its parent and add it to children
      const parentNode = messageMap.get(message.parent_id);
      if (parentNode) {
        parentNode.items.push(messageNode);
      } else {
        // Parent not found - treat as root message
        console.warn(`Parent message ${message.parent_id} not found for message ${message.id}`);
        rootMessages.push(messageNode);
      }
    }
  });

  return rootMessages;
};

const handleMessageClick = (message) => {
  activeMessage.value = message;
  // updateMessage(message.id, { read: true });
};
</script>
