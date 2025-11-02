<template>
  <div>
    <div class="h-screen w-screen bg-gray-500 sm:px-8 sm:py-8 md:px-16">
      <main class="container mx-auto h-full max-w-screen-lg">
        <!-- file upload modal -->
        <article
          aria-label="File Upload Modal"
          class="relative flex h-full flex-col rounded-md bg-white shadow-xl"
          ondrop="dropHandler(event);"
          ondragover="dragOverHandler(event);"
          ondragleave="dragLeaveHandler(event);"
          ondragenter="dragEnterHandler(event);"
        >
          <!-- overlay -->
          <div
            id="overlay"
            class="pointer-events-none absolute left-0 top-0 z-50 flex h-full w-full flex-col items-center justify-center rounded-md"
          >
            <i>
              <svg
                class="mb-3 h-12 w-12 fill-current text-theme-700"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
              >
                <path
                  d="M19.479 10.092c-.212-3.951-3.473-7.092-7.479-7.092-4.005 0-7.267 3.141-7.479 7.092-2.57.463-4.521 2.706-4.521 5.408 0 3.037 2.463 5.5 5.5 5.5h13c3.037 0 5.5-2.463 5.5-5.5 0-2.702-1.951-4.945-4.521-5.408zm-7.479-1.092l4 4h-3v4h-2v-4h-3l4-4z"
                />
              </svg>
            </i>
            <p class="text-lg text-theme-700">
              {{ $t("drag files here to upload") }}
            </p>
          </div>

          <!-- scroll area -->
          <section class="flex h-full w-full flex-col overflow-auto p-8">
            <header
              class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 py-12"
            >
              <p class="mb-3 flex flex-wrap justify-center font-semibold text-gray-900">
                <span>{{ $t("drag and drop") }} </span>&nbsp;
                <span>
                  {{ $t("your files anywhere") }}
                  {{ $t("or") }}
                </span>
              </p>
              <input id="hidden-input" type="file" multiple class="hidden" />
              <button
                id="button"
                class="mt-2 rounded-sm bg-gray-100 px-3 py-1 hover:bg-gray-200 focus:outline-none focus:ring"
              >
                {{ $t("upload file") }}
              </button>
            </header>

            <h1 class="pb-3 pt-8 font-semibold text-gray-900 sm:text-lg">
              {{ $t("to upload") }}
            </h1>

            <ul id="gallery" class="-m-1 flex flex-1 flex-wrap">
              <li
                id="empty"
                class="flex h-full w-full flex-col items-center justify-center text-center"
              >
                <img
                  class="mx-auto w-32"
                  src="https://user-images.githubusercontent.com/507615/54591670-ac0a0180-4a65-11e9-846c-e55ffce0fe7b.png"
                  alt="no data"
                />
                <span class="text-small text-gray-500">
                  {{ $t("no files") }}
                </span>
              </li>
            </ul>
          </section>

          <!-- sticky footer -->
          <footer class="flex justify-end px-8 pb-8 pt-4">
            <button
              id="submit"
              class="rounded-sm bg-theme-700 px-3 py-1 text-themecontrast-700 hover:bg-theme-400 focus:outline-none focus:ring"
            >
              {{ $t("upload now") }}
            </button>
            <button
              id="cancel"
              class="ml-3 rounded-sm px-3 py-1 hover:bg-gray-300 focus:outline-none focus:ring"
            >
              {{ $t("cancel") }}
            </button>
          </footer>
        </article>
      </main>
    </div>

    <!-- using two similar templates for simplicity in js code -->
    <template id="file-template">
      <li class="xl:w-1/8 block h-24 w-1/2 p-1 sm:w-1/3 md:w-1/4 lg:w-1/6">
        <article
          tabindex="0"
          class="elative group relative h-full w-full cursor-pointer rounded-md bg-gray-100 shadow-sm focus:outline-none focus:ring"
        >
          <img
            alt="upload preview"
            class="img-preview sticky hidden h-full w-full rounded-md bg-fixed object-cover"
          />

          <section
            class="absolute top-0 z-20 flex h-full w-full flex-col break-words rounded-md px-3 py-2 text-xs"
          >
            <h1 class="flex-1 group-hover:text-theme-800" />
            <div class="flex">
              <span class="p-1 text-theme-800">
                <i>
                  <svg
                    class="ml-auto h-4 w-4 fill-current pt-1"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                  >
                    <path d="M15 2v5h5v15h-16v-20h11zm1-2h-14v24h20v-18l-6-6z" />
                  </svg>
                </i>
              </span>
              <p class="size p-1 text-xs text-gray-700" />
              <button
                class="delete ml-auto rounded-md p-1 text-gray-800 hover:bg-gray-300 focus:outline-none"
              >
                <svg
                  class="pointer-events-none ml-auto h-4 w-4 fill-current"
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                >
                  <path
                    class="pointer-events-none"
                    d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z"
                  />
                </svg>
              </button>
            </div>
          </section>
        </article>
      </li>
    </template>

    <template id="image-template">
      <li class="xl:w-1/8 block h-24 w-1/2 p-1 sm:w-1/3 md:w-1/4 lg:w-1/6">
        <article
          tabindex="0"
          class="hasImage group relative h-full w-full cursor-pointer rounded-md bg-gray-100 text-transparent shadow-sm hover:text-white focus:outline-none focus:ring"
        >
          <img
            alt="upload preview"
            class="img-preview sticky h-full w-full rounded-md bg-fixed object-cover"
          />

          <section
            class="absolute top-0 z-20 flex h-full w-full flex-col break-words rounded-md px-3 py-2 text-xs"
          >
            <h1 class="flex-1" />
            <div class="flex">
              <span class="p-1">
                <i>
                  <svg
                    class="pt- ml-auto h-4 w-4 fill-current"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M5 8.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5zm9 .5l-2.519 4-2.481-1.96-4 5.96h14l-5-8zm8-4v14h-20v-14h20zm2-2h-24v18h24v-18z"
                    />
                  </svg>
                </i>
              </span>

              <p class="size p-1 text-xs" />
              <button class="delete ml-auto rounded-md p-1 hover:bg-gray-300 focus:outline-none">
                <svg
                  class="pointer-events-none ml-auto h-4 w-4 fill-current"
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                >
                  <path
                    class="pointer-events-none"
                    d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z"
                  />
                </svg>
              </button>
            </div>
          </section>
        </article>
      </li>
    </template>
  </div>
</template>

<script>
export default {
  mounted() {
    const fileTempl = document.getElementById("file-template"),
      imageTempl = document.getElementById("image-template"),
      empty = document.getElementById("empty");

    // use to store pre selected files
    let FILES = {};

    // check if file is of type image and prepend the initialied
    // template to the target element
    function addFile(target, file) {
      const isImage = file.type.match("image.*"),
        objectURL = URL.createObjectURL(file);

      const clone = isImage
        ? imageTempl.content.cloneNode(true)
        : fileTempl.content.cloneNode(true);

      clone.querySelector("h1").textContent = file.name;
      clone.querySelector("li").id = objectURL;
      clone.querySelector(".delete").dataset.target = objectURL;
      clone.querySelector(".size").textContent =
        file.size > 1024
          ? file.size > 1048576
            ? Math.round(file.size / 1048576) + "mb"
            : Math.round(file.size / 1024) + "kb"
          : file.size + "b";

      isImage &&
        Object.assign(clone.querySelector("img"), {
          src: objectURL,
          alt: file.name,
        });

      empty.classList.add("hidden");
      target.prepend(clone);

      FILES[objectURL] = file;
    }

    const gallery = document.getElementById("gallery"),
      overlay = document.getElementById("overlay");

    // click the hidden input of type file if the visible button is clicked
    // and capture the selected files
    const hidden = document.getElementById("hidden-input");
    document.getElementById("button").onclick = () => hidden.click();
    hidden.onchange = (e) => {
      for (const file of e.target.files) {
        addFile(gallery, file);
      }
    };

    // use to check if a file is being dragged
    const hasFiles = ({ dataTransfer: { types = [] } }) => types.indexOf("Files") > -1;

    // use to drag dragenter and dragleave events.
    // this is to know if the outermost parent is dragged over
    // without issues due to drag events on its children
    let counter = 0;

    // reset counter and append file to gallery when file is dropped
    function dropHandler(ev) {
      ev.preventDefault();
      for (const file of ev.dataTransfer.files) {
        addFile(gallery, file);
        overlay.classList.remove("draggedover");
        counter = 0;
      }
    }

    // only react to actual files being dragged
    function dragEnterHandler(e) {
      e.preventDefault();
      if (!hasFiles(e)) {
        return;
      }
      ++counter && overlay.classList.add("draggedover");
    }

    function dragLeaveHandler(e) {
      1 > --counter && overlay.classList.remove("draggedover");
    }

    function dragOverHandler(e) {
      if (hasFiles(e)) {
        e.preventDefault();
      }
    }

    // event delegation to caputre delete events
    // fron the waste buckets in the file preview cards
    gallery.onclick = ({ target }) => {
      if (target.classList.contains("delete")) {
        const ou = target.dataset.target;
        document.getElementById(ou).remove(ou);
        gallery.children.length === 1 && empty.classList.remove("hidden");
        delete FILES[ou];
      }
    };

    // print all selected files
    document.getElementById("submit").onclick = () => {
      alert(`Submitted Files:\n${JSON.stringify(FILES)}`);
    };

    // clear entire selection
    document.getElementById("cancel").onclick = () => {
      while (gallery.children.length > 0) {
        gallery.lastChild.remove();
      }
      FILES = {};
      empty.classList.remove("hidden");
      gallery.append(empty);
    };
  },
};
</script>

<style>
.hasImage:hover section {
  background-color: rgba(5, 5, 5, 0.4);
}
.hasImage:hover button:hover {
  background: rgba(5, 5, 5, 0.45);
}

#overlay p,
i {
  opacity: 0;
}

#overlay.draggedover {
  background-color: rgba(255, 255, 255, 0.7);
}
#overlay.draggedover p,
#overlay.draggedover i {
  opacity: 1;
}

.group:hover .group-hover\:text-theme-800 {
  color: #2b6cb0;
}
</style>
