<template>
  <div class="p-4">
    <!-- <pre>{{ editor.drawflow.drawflow.Home.data}}</pre> -->
    <!-- <div class="h-60 w-60 bg-emerald-100"></div> -->
    <div class="flex items-start">
      <div v-if="Object.keys(blueprintStatuses).length > 0" class="mr-4 w-1/6">
        <h2 class="flex justify-between text-sm font-bold tracking-wide">
          <nuxt-link
            class="mr-4 font-normal normal-case tracking-normal text-theme-500"
            to="/manage/blueprint-system/blueprints"
          >
            <font-awesome-icon :icon="['fal', 'chevron-left']" />
            {{ $t("back") }}
          </nuxt-link>

          <font-awesome-icon
            v-tooltip="
              //prettier-ignore
              $t('Drag nodes on the canvas and link them to create your blueprint. Be sure to add action or transition nodes to make them dynamic!')
            "
            :icon="['fal', 'circle-info']"
          />
        </h2>
        <h3 class="mt-4 text-sm font-bold uppercase tracking-wide">
          {{ $t("sequential statuses") }}
        </h3>
        <div class="mt-2 max-h-60 overflow-y-auto border-b-2 pr-4">
          <!-- 'border-cyan-300': status.code === 300,
								'border-sky-300': status.code === 301,
								'border-blue-300': status.code === 302,
								'border-amber-300': status.code === 318,
								'border-yellow-300': status.code === 303,
								'border-lime-300': status.code === 306,
								'border-green-300': status.code === 304,
								'border-emerald-300': status.code === 307, -->
          <div
            v-for="status in blueprintStatuses.sequential_item_statuses"
            :key="status.name"
            class="drag-drawflow my-3 cursor-move rounded-full border-2 border-white p-1 text-center text-sm"
            :class="[
              {
                'text-cyan-500': status.code === 300,
                'text-sky-500': status.code === 301,
                'text-blue-500': status.code === 302,
                'text-amber-500': status.code === 318,
                'text-yellow-500': status.code === 303,
                'text-lime-500': status.code === 306,
                'text-green-500': status.code === 304,
                'text-emerald-500': status.code === 307,
                'bg-cyan-100': status.code === 300,
                'bg-sky-100': status.code === 301,
                'bg-blue-100': status.code === 302,
                'bg-amber-100': status.code === 318,
                'bg-yellow-100': status.code === 303,
                'bg-lime-100': status.code === 306,
                'bg-green-100': status.code === 304,
                'bg-emerald-100': status.code === 307,

                'shadow-cyan-500': status.code === 300,
                'shadow-blue-500': status.code === 302,
                'shadow-amber-500': status.code === 318,
                'shadow-yellow-500': status.code === 303,
                'shadow-lime-500': status.code === 306,
                'shadow-green-500': status.code === 304,
                'shadow-emerald-500': status.code === 307,
              },
            ]"
            draggable="true"
            :data-node="status.name"
            @dragstart="
              drag(
                $event,
                status.color,
                'sequential_item_statuses',
                `bg-${status.color}-200 text-${status.color}-500`,
                status.code,
              )
            "
          >
            {{ status.name }}
          </div>
        </div>

        <h3 class="mt-8 text-sm font-bold uppercase tracking-wide">
          {{ $t("deviant statuses") }}
        </h3>
        <div class="mt-2 max-h-60 overflow-y-auto border-b-2 pr-4">
          <!-- 'border-cyan-300': status.code === 317,
								'border-blue-300': status.code === 309,
								'border-indigo-300': status.code === 312,
								'border-violet-300': status.code === 313,
								'border-purple-300': status.code === 314,
								'border-fuchsia-300': status.code === 315,
								'border-pink-300': status.code === 316,
								'border-rose-300': status.code === 319,
								'border-red-300': status.code === 305,
								'border-orange-300': status.code === 311, -->
          <div
            v-for="status in blueprintStatuses.deviant_statuses"
            :key="status.name"
            class="drag-drawflow my-3 cursor-move rounded-full border-2 border-white p-1 text-center text-sm"
            :class="[
              {
                'text-cyan-500': status.code === 317,
                'text-blue-500': status.code === 309,
                'text-indigo-500': status.code === 312,
                'text-violet-500': status.code === 313,
                'text-purple-500': status.code === 314,
                'text-fuchsia-500': status.code === 315,
                'text-pink-500': status.code === 316,
                'text-rose-500': status.code === 319,
                'text-red-500': status.code === 305,
                'text-orange-300': status.code === 311,

                'bg-cyan-100': status.code === 317,
                'bg-blue-100': status.code === 309,
                'bg-indigo-100': status.code === 312,
                'bg-violet-100': status.code === 313,
                'bg-purple-100': status.code === 314,
                'bg-fuchsia-100': status.code === 315,
                'bg-pink-100': status.code === 316,
                'bg-rose-100': status.code === 319,
                'bg-red-100': status.code === 305,
                'bg-orange-100': status.code === 311,
                'shadow-cyan-500': status.code === 317,
                'shadow-blue-500': status.code === 309,
                'shadow-indigo-500': status.code === 312,
                'shadow-violet-500': status.code === 313,
                'shadow-purple-500': status.code === 314,
                'shadow-fuchsia-500': status.code === 315,
                'shadow-pink-500': status.code === 316,
                'shadow-rose-500': status.code === 319,
                'shadow-red-500': status.code === 305,
                'shadow-orange-500': status.code === 311,
              },
            ]"
            draggable="true"
            :data-node="status.name"
            @dragstart="
              drag(
                $event,
                status.color,
                'deviant_item_statuses',
                `bg-${status.color}-200 text-${status.color}-500`,
                status.code,
              )
            "
          >
            {{ status.name }}
          </div>
        </div>

        <h3 class="mt-8 text-sm font-bold uppercase tracking-wide">
          {{ $t("Actions and transitions") }}
        </h3>
        <div class="mt-2 max-h-60 pr-4">
          <div
            v-for="action in actions"
            :key="action.name"
            :class="`drag-drawflow mb-1 mt-2 rounded-full border-2 bg-white p-1 text-center text-sm font-bold`"
            draggable="true"
            :data-node="action.name"
            @dragstart="drag($event, action.color, 'action', 'action', action.icon)"
          >
            <font-awesome-icon :icon="['fal', action.icon]" />
            {{ action.name }}
          </div>
        </div>

        <!-- <div class="p-4 overflow-auto border rounded">
					<code>
						<pre>{{ export_data }}</pre>
					</code>
				</div> -->
      </div>

      <div class="menu w-5/6">
        <!-- <ul
					class="flex justify-center w-full overflow-x-auto"
					v-if="editor"
				>
					<li
						v-for="(module, name, i) in editor.drawflow.drawflow"
						:key="name + '_' + i"
						@click="editor.changeModule(name), changeModule(name)"
						class="px-3 py-1 mx-1 text-sm transition-colors duration-200 rounded-t cursor-pointer tab focus:outline-none group"
						:class="{
							'text-themecontrast-400 font-semibold bg-theme-400 dark:bg-theme-400 dark:hover:bg-theme-400':
								activeModule === name,
							'bg-theme-100 dark:bg-theme-400 hover:bg-theme-200 dark:hover:bg-theme-400':
								activeModule !== name,
						}"
					>
						{{ name }}
					</li>
					<li>
						<button
							class="px-3 transition text-theme-400 hover:text-theme-600"
						>
							+ {{ $t("new") }}
						</button>
					</li>
				</ul> -->

        <div
          id="drawflow"
          class="relative flex w-full flex-col overflow-auto rounded-md bg-white shadow-md shadow-gray-200 dark:shadow-gray-900"
          style="height: 89vh"
          @drop="drop"
          @dragover="allowDrop"
        >
          <BluePrint_new />
        </div>
      </div>

      <BluePrintActionPanel />
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      blueprintStatuses: {},
      nodeType: "",
      classList: "",
      mobile_item_selec: "",
      mobile_last_move: null,
      color: "",
      icon: "",
      actions: [
        {
          name: "Action",
          class: "action",
          icon: "gear",
        },
        {
          name: "Transition",
          class: "action",
          icon: "arrows-spin",
        },
      ],
      activeModule: "",
    };
  },
  created() {
    this.$nuxt.$on("save", (e) => this.saveBlueprint(e));
  },
  mounted() {
    const self = this;
    if (this.statuses) {
      this.blueprintStatuses = { ...this.statuses };

      if (this.data_to_import && Object.keys(this.data_to_import).length > 0) {
        this.prepare();
      }
    }

    if (this.editor) {
      // if (this.editor.drawflow?.drawflow) {
      // 	this.activeModule = Object.keys(this.editor.drawflow.drawflow)[0];
      // }

      this.editor.on("nodeRemoved", function (id) {
        // console.log("Node removed " + id);
        // console.log(self.selected_node);
        if (self.selected_node.type === "sequential_item_statuses") {
          self.blueprintStatuses.sequential_item_statuses.push(self.selected_node);
        }
      });

      this.editor.on("connectionCreated", function (connection) {
        // console.log("Connection created");
        // console.log(connection);

        const node_from = self.editor.getNodeFromId(parseInt(connection.output_id));
        const node_to = self.editor.getNodeFromId(parseInt(connection.input_id));

        // console.log(node_from);
        // console.log(node_to);
        let count = 0;

        if (
          node_from.name === "Action" ||
          node_to.name === "Action" ||
          node_from.name === "Transition" ||
          node_to.name === "Transition"
        ) {
          node_from.outputs.output_1.connections.forEach((connection) => {
            const node = self.editor.getNodeFromId(connection.node);
            // console.log(node.name);
            if (node.name === "Transition" || node.name === "Action") {
              count++;
            }
          });

          if (count > 1) {
            self.set_toast({
              text: $t("you can only attach to an Action OR a Transition"),
              status: "orange",
            });

            self.editor.removeSingleConnection(
              connection.output_id,
              connection.input_id,
              connection.output_class,
              connection.input_class,
            );
          } else {
            return;
          }
        } else {
          self.set_toast({
            text: $t("you need to add an action in between"),
            status: "orange",
          });

          self.editor.removeSingleConnection(
            connection.output_id,
            connection.input_id,
            connection.output_class,
            connection.input_class,
          );
        }
      });
    }
  },
  beforeUnmount() {
    this.$nuxt.$off("save");
  },
  computed: {
    ...mapState({
      statuses: (state) => state.statuses.statuses,
      editor: (state) => state.blueprint.editor,
      export_data: (state) => state.blueprint.export_data,
      data_to_import: (state) => state.blueprint.data_to_import,
      selected_node: (state) => state.blueprint.selected_node,
      selected_blueprint: (state) => state.blueprint.selected_blueprint,
    }),

    // ...mapGetters({
    //    statusColor: 'statuses/statusColor'
    // })
  },
  watch: {
    editor: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    selected_node: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    data_to_import: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  methods: {
    prepare() {
      // console.log("preparing");
      Object.keys(this.data_to_import).forEach((element) => {
        for (const key in this.data_to_import[element].data) {
          if (Object.hasOwnProperty.call(this.data_to_import[element].data, key)) {
            const node = this.data_to_import[element].data[key];
            let i = 0;
            i = this.blueprintStatuses.sequential_item_statuses.findIndex(
              (status) => status.name === node.name,
            );

            if (i > 0) {
              this.blueprintStatuses.sequential_item_statuses.splice(i, 1);
            }
          }
        }
      });
    },

    drag(ev, color, nodeType, classlist, icon, code) {
      this.color = color;
      this.nodeType = nodeType;
      this.classList = classlist;
      this.icon = icon;
      this.code = code;

      // console.log(classlist);
      // console.log(icon);
      // console.log(this.icon);
      // console.log(this.nodeType);
      // console.log(this.code);

      if (ev.type === "touchstart") {
        this.mobile_item_selec = ev.target.closest(".drag-drawflow").getAttribute("data-node");
      } else {
        ev.dataTransfer.setData("node", ev.target.getAttribute("data-node"));
      }
    },

    drop(ev) {
      if (ev.type === "touchend") {
        const parentdrawflow = document
          .elementFromPoint(
            this.mobile_last_move.touches[0].clientX,
            this.mobile_last_move.touches[0].clientY,
          )
          .closest("#drawflow");
        if (parentdrawflow != null) {
          this.addNodeToDrawFlow(
            this.mobile_item_selec,
            this.mobile_last_move.touches[0].clientX,
            this.mobile_last_move.touches[0].clientY,
          );
        }
        this.mobile_item_selec = "";
      } else {
        ev.preventDefault();
        const data = ev.dataTransfer.getData("node");
        this.addNodeToDrawFlow(data, ev.clientX, ev.clientY);
      }
    },

    addNodeToDrawFlow(name, pos_x, pos_y) {
      if (this.editor && this.editor.precanvas) {
        if (this.editor.editor_mode === "fixed") {
          return false;
        }
        pos_x =
          pos_x *
            (this.editor.precanvas.clientWidth /
              (this.editor.precanvas.clientWidth * this.editor.zoom)) -
          this.editor.precanvas.getBoundingClientRect().x *
            (this.editor.precanvas.clientWidth /
              (this.editor.precanvas.clientWidth * this.editor.zoom));
        pos_y =
          pos_y *
            (this.editor.precanvas.clientHeight /
              (this.editor.precanvas.clientHeight * this.editor.zoom)) -
          this.editor.precanvas.getBoundingClientRect().y *
            (this.editor.precanvas.clientHeight /
              (this.editor.precanvas.clientHeight * this.editor.zoom));
        let html = `
               <div>
                  <div class="text-${this.color}-500 bg-${this.color}-100 p-1 border-2 border-white text-sm rounded-full text-center">${name}</div>
               </div>
            `;

        if (this.nodeType === "action") {
          html = `
               <div class="w-full text-center" v-tooltip="'right click to manage'">
                  <img
                     class="w-6 h-6 mx-auto"
                     src="/img/${this.icon}.svg"
                  />
               </div>`;
        }

        this.editor.addNode(
          name,
          1,
          1,
          pos_x,
          pos_y,
          this.classList?.length > 0 ? this.classList : "",
          { color: this.color, type: this.nodeType, code: this.code },
          html,
        );

        if (this.nodeType === "sequential_item_statuses") {
          let i = 0;
          i = this.blueprintStatuses.sequential_item_statuses.findIndex(
            (status) => status.name === name,
          );
          this.blueprintStatuses.sequential_item_statuses.splice(i, 1);
        }
        // if (this.nodeType === "deviant_statuses") {
        // 	let i = 0;
        // 	i = this.blueprintStatuses.deviant_statuses.findIndex(
        // 		(status) => status.name === name
        // 	);
        // 	console.log(i);
        // 	this.blueprintStatuses.deviant_statuses.splice(i, 1);
        // }
      }
    },
    allowDrop(ev) {
      ev.preventDefault();
    },

    remove(output, input) {
      this.editor.removeSingleConnection(input, output);
    },

    changeModule(name) {
      this.activeModule = name;
      // var all = document.querySelectorAll(".menu ul li");
      // for (var i = 0; i < all.length; i++) {
      // 	all[i].classList.remove("selected");
      // }
      // event.target.classList.add("selected");
    },

    saveBlueprint(blueprintdata) {
      this.api
        .put(`orders/settings/blueprints/${this.selected_blueprint.id}`, {
          name: this.selected_blueprint.name,
          blueprint: [blueprintdata],
          ns: "system",
        })
        .then((response) => {
          this.handleSuccess(response);
        })
        .catch((error) => {
          this.handleError(error);
          // TODO:  if blueprint not exists, create new one
        });
    },
  },
};
</script>
