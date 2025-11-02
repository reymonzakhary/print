<template>
  <main class="grid w-full grid-cols-12 gap-4 p-4">
    <header class="flex justify-between mb-2 w-full col-span-12">
      <UICardHeaderTitle title="Messages" :icon="['fal', 'envelopes-bulk']" />
    </header>
    <aside class="col-span-4">
      <h1 class="font-bold uppercase tracking-wide">
        <font-awesome-icon class="mr-2" :icon="['fal', 'inbox-full']" />
        Inbox
      </h1>
      <div v-if="messages?.length > 0" class="space-y-4">
        <ul role="list" class="divide-y">
          <li
            v-for="message in messages"
            :key="message.id"
            class="relative flex cursor-pointer justify-between gap-x-6 rounded p-4 hover:bg-gray-200 dark:hover:bg-gray-900"
            :class="{ 'bg-theme-50 !text-theme-500': activeMessage === message }"
            @click="handleMessageClick(message)"
          >
            <div class="flex min-w-0 gap-x-4">
              <div v-if="message.type === 'producer'" class="flex flex-col items-center gap-x-2">
                <font-awesome-icon
                  v-tooltip="message.type"
                  class="my-auto size-8 flex-none"
                  :icon="['fal', 'file-signature']"
                  aria-hidden="true"
                />
                <UIButton class="ml-2" to="/contracts">
                  Contract
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
      <p v-else-if="!isLoading">No messages found.</p>
      <p v-else>
        <UIListSkeleton
          :key="'skeleton1'"
          :skeleton-line-height="12"
          :skeleton-line-amount="5"
          class="w-full"
        />
      </p>
    </aside>

    <UICard v-if="activeMessage" class="col-span-8" rounded-full>
      <UICardHeader class="sticky top-0 z-10 !justify-between" :background-color="false">
        <template #left>
          <div class="flex items-center gap-2 dark:text-theme-50">
            <UICardHeaderTitle
              :icon="['fal', 'envelope-open-text']"
              :title="activeMessage.subject"
            />
            from
            <span class="font-bold">{{ activeMessage.sender_name }}</span>
            <span class="text-gray-500 dark:text-gray-400">{{ activeMessage.sender_email }}</span>
            <date :datetime="activeMessage.created_at" class="text-gray-500 dark:text-gray-400">
              {{ new Date(activeMessage.created_at).toLocaleDateString() }}
            </date>
          </div>
        </template>
        <template #right>
          <div class="w-full text-right dark:text-theme-50">
            <span class="pr-2 font-bold uppercase tracking-wide">
              {{ activeMessage.type }}
            </span>
            <UIButton v-if="activeMessage.type === 'producer'" class="ml-2" to="/contracts">
              Open Contract
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
          <template v-if="message.type === 'producer'">
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
                    <span
                      v-if="message.subject"
                      class="font-medium text-gray-900 dark:text-theme-50 mr-2 uppercase"
                      >{{ message.subject }}</span
                    >
                    <span class="font-medium text-gray-900 dark:text-theme-50">{{
                      message.sender_name
                    }}</span>
                    sent
                  </template>
                  <template v-else>
                    <span class="font-medium text-gray-900 dark:text-theme-50">
                      you
                      {{ message.resipient_email }}
                    </span>
                    replied
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

              <!-- <UIButton
                v-if="message.from === 'sender' && index === activeMessage.items.length - 1"
                class="mt-3"
                @click="reply"
              >
                Reply
              </UIButton> -->

              <UIButton
                v-if="message.type === 'producer' && index === activeMessage.items.length - 1"
                to="/contracts"
                class="mt-3 flex flex-col !items-start !rounded !px-4 !text-base shadow-md"
              >
                <div class="font-base font-bold text-black dark:text-theme-50">
                  <font-awesome-icon :icon="['fal', 'file-signature']" aria-hidden="true" />
                  {{ message.type }}
                </div>
                <div class="text-sm/6">
                  open contract
                  <font-awesome-icon
                    :icon="['fal', 'external-link']"
                    class="ml-1"
                    aria-hidden="true"
                  />
                </div>
              </UIButton>
            </div>
          </template>
        </li>
      </ul>

      <!-- New comment form -->
      <div class="mt-6 p-4 flex gap-x-3">
        <PrindustryLogo class="size-6 flex-none bg-white text-cyan-600" />
        <form action="#" class="relative flex-auto">
          <div
            class="overflow-hidden rounded-lg pb-12 outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600"
          >
            <label for="comment" class="sr-only">Add your reply</label>
            <textarea
              id="comment"
              rows="2"
              name="comment"
              class="block w-full resize-none bg-transparent px-3 py-1.5 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6"
              placeholder="Add your reply..."
            />
          </div>

          <div class="absolute inset-x-0 bottom-0 flex justify-between py-2 pl-3 pr-2">
            <div class="flex items-center space-x-5">
              <div class="flex items-center">
                <button
                  type="button"
                  class="-m-2.5 flex size-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-400"
                >
                  <font-awesome-icon
                    :icon="['fal', 'paperclip']"
                    class="size-5"
                    aria-hidden="true"
                  />
                  <span class="sr-only">Attach a file</span>
                </button>
              </div>
            </div>
            <button
              type="submit"
              class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
              Reply
            </button>
          </div>
        </form>
      </div>
    </UICard>

    <MessageZeroState v-else :message="'No message selected'" class="col-span-8" />
  </main>
</template>
<script setup>
import PrindustryLogo from "~/components/global/PrindustryLogo.vue";

const { fetchMessages, updateMessage } = useMessagesRepository();
const { handleError } = useMessageHandler();
// const { theUser: me } = storeToRefs(useAuthStore);

// State variables
const messages = ref([]);
const activeMessage = ref(null);
const isLoading = ref(true);

// Lifecycle hooks
onMounted(() => {
  fetchMessages()
    .then((response) => {
      messages.value = groupMessagesBySender(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
    });
});

// Methods
const groupMessagesBySender = (messages) => {
  // First, create a map of all messages by their ID for quick lookup
  const messagesById = messages.reduce((map, message) => {
    map[message.id] = { ...message, items: [] };
    return map;
  }, {});

  // Initialize root messages array (messages without parent)
  const rootMessages = [];

  // Process each message to build the hierarchy
  messages.forEach((message) => {
    const messageWithItems = messagesById[message.id];

    if (message.parent_id) {
      // This is a child message, add it to its parent's items
      if (messagesById[message.parent_id]) {
        messagesById[message.parent_id].items.push(messageWithItems);
      } else {
        // If parent doesn't exist (unusual case), add to root
        rootMessages.push(messageWithItems);
      }
    } else {
      // No parent, this is a root message
      rootMessages.push(messageWithItems);
    }
  });

  // Sort messages by path if available, otherwise by created_at
  return rootMessages.sort((a, b) => {
    if (a.path && b.path) return a.path.localeCompare(b.path);
    return new Date(b.created_at) - new Date(a.created_at);
  });
};

const handleMessageClick = (message) => {
  activeMessage.value = message;
  // updateMessage(message.id, { read: true });
};
</script>
