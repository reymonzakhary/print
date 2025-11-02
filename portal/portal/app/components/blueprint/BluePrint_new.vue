<template>
  <div
    class="sticky top-0 z-10 flex items-center justify-between w-full h-auto p-2 text-sm bg-white rounded-t-md"
  >
    <div class="flex items-center">
      <div class="mx-2">
        <font-awesome-icon
          id="lock"
          :icon="['fal', 'lock']"
          class="cursor-pointer text-theme-400"
          @click="
            editor.editor_mode = 'fixed';
            changeMode('lock');
          "
        />
        <font-awesome-icon
          id="unlock"
          :icon="['fal', 'lock-open']"
          class="cursor-pointer text-theme-400"
          style="display: none"
          @click="
            editor.editor_mode = 'edit';
            changeMode('unlock');
          "
        />
      </div>

      <button
        class="flex items-center px-2 mx-2 transition bg-white rounded-full cursor-pointer btn-export text-theme-400 hover:text-theme-600"
        @click="populate_export_data(JSON.stringify(editor.export(), null, 4))"
      >
        <font-awesome-icon :icon="['fal', 'cloud-arrow-down']" class="mr-1" />
        {{ $t("export") }}
      </button>

      <button
        class="flex items-center px-2 mx-2 text-red-400 transition bg-white rounded-full btn-clear hover:text-red-600"
        @click="editor.clearModuleSelected()"
      >
        <font-awesome-icon :icon="['fal', 'transporter-7']" class="mr-1" />
        {{ $t("clear") }}
      </button>
    </div>
    <div class="flex mr-2">
      <font-awesome-icon
        :icon="['fal', 'magnifying-glass-minus']"
        class="text-white cursor-pointer"
        @click="editor.zoom_out()"
      />
      <font-awesome-icon
        :icon="['fal', 'magnifying-glass']"
        class="mx-2 text-white cursor-pointer"
        @click="editor.zoom_reset()"
      />
      <font-awesome-icon
        :icon="['fal', 'magnifying-glass-plus']"
        class="text-white cursor-pointer"
        @click="editor.zoom_in()"
      />
    </div>
    <div>
      <button
        class="flex items-center px-4 py-1 mx-2 text-white transition bg-green-500 rounded-full cursor-pointer btn-export hover:bg-green-600"
        @click.once="eventStore.emit('floppy-disk', editor.export())"
      >
        <font-awesome-icon :icon="['fad', 'floppy-disk']" class="mr-2" />
        {{ $t("save") }}
      </button>
    </div>
  </div>
</template>

<script>
import Drawflow from "drawflow";
import { mapState, mapMutations } from "vuex";

export default {
  setup() {
    const eventStore = useEventStore();
    return { eventStore };
  },
  computed: {
    ...mapState({
      editor: (state) => state.blueprint.editor,
      data_to_import: (state) => state.blueprint.data_to_import,
      selected_node: (state) => state.blueprint.selected_node,
      export_data: (state) => state.blueprint.export_data,
    }),
  },
  watch: {
    editor: {
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
  mounted() {
    this.initiateDrawflow();
  },
  beforeUnmount() {
    this.eventStore.off("save");
  },
  methods: {
    ...mapMutations({
      set_editor: "blueprint/set_editor",
      select_node: "blueprint/select_node",
      toggle_actionpanel: "blueprint/toggle_actionpanel",
      populate_settings: "blueprint/populate_settings",
      populate_export_data: "blueprint/populate_export_data",
    }),

    initiateDrawflow() {
      const self = this;
      const id = document.getElementById("drawflow");
      this.set_editor(new Drawflow(id));

      // disable selection on right mouse click
      this.editor.contextmenu = (e) => {
        self.editor.ele_selected = null;
        self.editor.dispatch("contextmenu", e);
        e.preventDefault();
      };

      this.editor.force_first_input = true;
      this.editor.start();

      if (this.data_to_import && Object.keys(this.data_to_import).length > 0) {
        this.editor.import(this.data_to_import);
      }

      this.editor.on("nodeSelected", function (id) {
        // console.log("Node selected " + id);

        const node = self.editor.getNodeFromId(id);

        self.select_node({
          name: node.name,
          color: node.data.color,
          type: node.data.type,
        });

        if (
          node.inputs.input_1.connections.length > 0 &&
          node.outputs.output_1.connections.length > 0
        ) {
          const node_from_id = parseInt(
            node.inputs.input_1.connections[0].node,
          );
          const node_to_id = parseInt(
            node.outputs.output_1.connections[0].node,
          );

          const node_from = self.editor.getNodeFromId(node_from_id);
          const node_to = self.editor.getNodeFromId(node_to_id);

          self.populate_settings({
            self: node,
            from: node_from,
            to: node_to,
          });
        }
      });

      this.editor.on("clickEnd", function (e) {
        if (e.target.classList[0] === "main-path") {
          self.editor.contextmenu(e);
        }
      });

      this.editor.on("contextmenu", function (e) {
        // console.log("context");
        // console.log(e);

        if (Object.keys(self.$store.state.blueprint.settings).length > 0) {
          self.toggle_actionpanel(true);
        }

        if (
          self.editor.editor_mode === "fixed" ||
          self.editor.editor_mode === "view"
        ) {
          return false;
        }
        if (
          self.editor.precanvas.getElementsByClassName("drawflow-delete").length
        ) {
          self.editor.precanvas
            .getElementsByClassName("drawflow-delete")[0]
            .remove();
        }
        if (self.editor.connection_selected) {
          const deletebox = document.createElement("div");
          deletebox.classList.add("drawflow-delete");
          deletebox.innerHTML = "x";
          if (
            self.editor.connection_selected &&
            self.editor.connection_selected.parentElement.classList.length > 1
          ) {
            deletebox.style.top =
              e.clientY *
                (self.editor.precanvas.clientHeight /
                  (self.editor.precanvas.clientHeight * self.editor.zoom)) -
              self.editor.precanvas.getBoundingClientRect().y *
                (self.editor.precanvas.clientHeight /
                  (self.editor.precanvas.clientHeight * self.editor.zoom)) +
              "px";
            deletebox.style.left =
              e.clientX *
                (self.editor.precanvas.clientWidth /
                  (self.editor.precanvas.clientWidth * self.editor.zoom)) -
              self.editor.precanvas.getBoundingClientRect().x *
                (self.editor.precanvas.clientWidth /
                  (self.editor.precanvas.clientWidth * self.editor.zoom)) +
              "px";

            self.editor.precanvas.appendChild(deletebox);
          }
        }
      });

      /* DRAG EVENT */

      /* Mouse and Touch Actions */
      const elements = document.getElementsByClassName("drag-drawflow");
      for (let i = 0; i < elements.length; i++) {
        elements[i].addEventListener("touchend", self.drop, false);
        elements[i].addEventListener("touchmove", self.positionMobile, false);
        elements[i].addEventListener("touchstart", self.drag, false);
      }

      this.editor.createCurvature = function (
        start_pos_x,
        start_pos_y,
        end_pos_x,
        end_pos_y,
        curvature_value,
        type,
      ) {
        const line_x = start_pos_x;
        const line_y = start_pos_y;
        const x = end_pos_x;
        const y = end_pos_y;
        const curvature = curvature_value;
        //type openclose open close other
        switch (type) {
          case "open":
            if (start_pos_x >= end_pos_x) {
              var hx1 = line_x + Math.abs(x - line_x) * curvature;
              var hx2 = x - Math.abs(x - line_x) * (curvature * -1);
            } else {
              var hx1 = line_x + Math.abs(x - line_x) * curvature;
              var hx2 = x - Math.abs(x - line_x) * curvature;
            }
            return (
              " M " +
              line_x +
              " " +
              line_y +
              " C " +
              hx1 +
              " " +
              line_y +
              " " +
              hx2 +
              " " +
              y +
              " " +
              x +
              "  " +
              y
            );

            break;
          case "close":
            if (start_pos_x >= end_pos_x) {
              var hx1 = line_x + Math.abs(x - line_x) * (curvature * -1);
              var hx2 = x - Math.abs(x - line_x) * curvature;
            } else {
              var hx1 = line_x + Math.abs(x - line_x) * curvature;
              var hx2 = x - Math.abs(x - line_x) * curvature;
            } //M0 75H10L5 80L0 75Z

            return (
              " M " +
              line_x +
              " " +
              line_y +
              " C " +
              hx1 +
              " " +
              line_y +
              " " +
              hx2 +
              " " +
              y +
              " " +
              x +
              "  " +
              y +
              " M " +
              (x - 11) +
              " " +
              y +
              " L" +
              (x - 20) +
              " " +
              (y - 5) +
              "  L" +
              (x - 20) +
              " " +
              (y + 5) +
              "Z"
            );
            break;
          case "other":
            if (start_pos_x >= end_pos_x) {
              var hx1 = line_x + Math.abs(x - line_x) * (curvature * -1);
              var hx2 = x - Math.abs(x - line_x) * (curvature * -1);
            } else {
              var hx1 = line_x + Math.abs(x - line_x) * curvature;
              var hx2 = x - Math.abs(x - line_x) * curvature;
            }
            return (
              " M " +
              line_x +
              " " +
              line_y +
              " C " +
              hx1 +
              " " +
              line_y +
              " " +
              hx2 +
              " " +
              y +
              " " +
              x +
              "  " +
              y
            );
            break;
          default:
            var hx1 = line_x + Math.abs(x - line_x) * curvature;
            var hx2 = x - Math.abs(x - line_x) * curvature;

            //return ' M '+ line_x +' '+ line_y +' C '+ hx1 +' '+ line_y +' '+ hx2 +' ' + y +' ' + x +'  ' + y;
            return (
              " M " +
              line_x +
              " " +
              line_y +
              " C " +
              hx1 +
              " " +
              line_y +
              " " +
              hx2 +
              " " +
              y +
              " " +
              x +
              "  " +
              y +
              " M " +
              (x - 11) +
              " " +
              y +
              " L" +
              (x - 20) +
              " " +
              (y - 5) +
              "  L" +
              (x - 20) +
              " " +
              (y + 5) +
              "Z"
            );
        }
      };
    },
    positionMobile(ev) {
      this.mobile_last_move = ev;
    },

    showpopup(e) {
      // console.log(e.target);
      e.target.closest(".drawflow-node").style.zIndex = "9999";
      e.target.children[0].style.display = "block";
      //document.getElementById("modalfix").style.display = "block";

      //e.target.children[0].style.this.transform = 'translate('+translate.x+'px, '+translate.y+'px)';
      this.transform = this.editor.precanvas.style.transform;
      this.editor.precanvas.style.transform = "";
      this.editor.precanvas.style.left = this.editor.canvas_x + "px";
      this.editor.precanvas.style.top = this.editor.canvas_y + "px";
      // console.log(this.transform);

      //e.target.children[0].style.top  =  -editor.canvas_y - editor.container.offsetTop +'px';
      //e.target.children[0].style.left  =  -editor.canvas_x  - editor.container.offsetLeft +'px';
      this.editor.editor_mode = "fixed";
    },

    closemodal(e) {
      e.target.closest(".drawflow-node").style.zIndex = "2";
      e.target.parentElement.parentElement.style.display = "none";
      //document.getElementById("modalfix").style.display = "none";
      this.editor.precanvas.style.transform = this.transform;
      this.editor.precanvas.style.left = "0px";
      this.editor.precanvas.style.top = "0px";
      this.editor.editor_mode = "edit";
    },

    changeMode(option) {
      //console.log(lock.id);
      if (option == "lock") {
        lock.style.display = "none";
        unlock.style.display = "block";
      } else {
        lock.style.display = "block";
        unlock.style.display = "none";
      }
    },
  },
};
</script>

<style lang="scss">
:root {
  --dfBackgroundColor: #ffffff;
  --dfBackgroundSize: 12px;
  --dfBackgroundImage: radial-gradient(rgb(235, 235, 235) 1px, transparent 1px);

  --dfNodeType: flex;
  --dfNodeTypeFloat: none;
  --dfNodeBackgroundColor: #ffffff;
  --dfNodeTextColor: #000000;
  --dfNodeBorderSize: 0px;
  --dfNodeBorderColor: #000000;
  --dfNodeBorderRadius: 60px;
  --dfNodeMinHeight: 30px;
  --dfNodeMinWidth: 160px;
  --dfNodePaddingTop: 0px;
  --dfNodePaddingLeft: 0px;
  --dfNodePaddingRight: 0px;
  --dfNodePaddingBottom: 0px;
  --dfNodeBoxShadowHL: 0px;
  --dfNodeBoxShadowVL: 2px;
  --dfNodeBoxShadowBR: 15px;
  --dfNodeBoxShadowS: 0px;
  --dfNodeBoxShadowColor: rgba(208, 208, 208, 1);

  --dfNodeHoverBackgroundColor: #ffffff;
  --dfNodeHoverTextColor: #000000;
  --dfNodeHoverBorderSize: 0px;
  --dfNodeHoverBorderColor: #000000;
  --dfNodeHoverBorderRadius: 60px;

  --dfNodeHoverBoxShadowHL: 0px;
  --dfNodeHoverBoxShadowVL: 2px;
  --dfNodeHoverBoxShadowBR: 15px;
  --dfNodeHoverBoxShadowS: 0px;
  --dfNodeHoverBoxShadowColor: rgba(168, 213, 255, 1);

  --dfNodeSelectedBackgroundColor: rgba(245, 245, 245, 1);
  --dfNodeSelectedTextColor: rgba(0, 0, 0, 1);
  --dfNodeSelectedBorderSize: 1px;
  --dfNodeSelectedBorderColor: rgba(78, 169, 255, 1);
  --dfNodeSelectedBorderRadius: 40px;

  --dfNodeSelectedBoxShadowHL: 0px;
  --dfNodeSelectedBoxShadowVL: 2px;
  --dfNodeSelectedBoxShadowBR: 15px;
  --dfNodeSelectedBoxShadowS: 2px;
  --dfNodeSelectedBoxShadowColor: rgba(78, 169, 255, 1);

  --dfInputBackgroundColor: rgba(78, 169, 255, 1);
  --dfInputBorderSize: 0px;
  --dfInputBorderColor: #000000;
  --dfInputBorderRadius: 50px;
  --dfInputLeft: -8px;
  --dfInputHeight: 15px;
  --dfInputWidth: 15px;

  --dfInputHoverBackgroundColor: rgba(29, 145, 255, 1);
  --dfInputHoverBorderSize: 1px;
  --dfInputHoverBorderColor: rgba(0, 131, 255, 1);
  --dfInputHoverBorderRadius: 50px;

  --dfOutputBackgroundColor: rgba(78, 169, 255, 1);
  --dfOutputBorderSize: 0px;
  --dfOutputBorderColor: #000000;
  --dfOutputBorderRadius: 50px;
  --dfOutputRight: 8px;
  --dfOutputHeight: 15px;
  --dfOutputWidth: 15px;

  --dfOutputHoverBackgroundColor: rgba(29, 145, 255, 1);
  --dfOutputHoverBorderSize: 1px;
  --dfOutputHoverBorderColor: rgba(0, 131, 255, 1);
  --dfOutputHoverBorderRadius: 50px;

  --dfLineWidth: 3px;
  --dfLineColor: rgba(182, 220, 255, 1);
  --dfLineHoverColor: rgba(101, 185, 255, 1);
  --dfLineSelectedColor: rgba(106, 255, 207, 1);

  --dfRerouteBorderWidth: 2px;
  --dfRerouteBorderColor: rgba(183, 183, 183, 1);
  --dfRerouteBackgroundColor: #ffffff;

  --dfRerouteHoverBorderWidth: 2px;
  --dfRerouteHoverBorderColor: #000000;
  --dfRerouteHoverBackgroundColor: #ffffff;

  --dfDeleteDisplay: block;
  --dfDeleteColor: #ffffff;
  --dfDeleteBackgroundColor: #000000;
  --dfDeleteBorderSize: 2px;
  --dfDeleteBorderColor: #ffffff;
  --dfDeleteBorderRadius: 50px;
  --dfDeleteTop: -15px;

  --dfDeleteHoverColor: #000000;
  --dfDeleteHoverBackgroundColor: #ffffff;
  --dfDeleteHoverBorderSize: 2px;
  --dfDeleteHoverBorderColor: #000000;
  --dfDeleteHoverBorderRadius: 50px;
}

#drawflow {
  background: var(--dfBackgroundColor);
  background-size: var(--dfBackgroundSize) var(--dfBackgroundSize);
  background-image: var(--dfBackgroundImage);
}

.drawflow .drawflow-node {
  display: var(--dfNodeType);
  background: var(--dfNodeBackgroundColor);
  color: var(--dfNodeTextColor);
  border: var(--dfNodeBorderSize) solid var(--dfNodeBorderColor);
  border-radius: var(--dfNodeBorderRadius);
  min-height: var(--dfNodeMinHeight);
  width: auto;
  min-width: var(--dfNodeMinWidth);
  padding-top: var(--dfNodePaddingTop);
  padding-bottom: var(--dfNodePaddingBottom);
  padding-left: var(--dfNodePaddingLeft);
  padding-right: var(--dfNodePaddingRight);
  -webkit-box-shadow: var(--dfNodeBoxShadowHL) var(--dfNodeBoxShadowVL)
    var(--dfNodeBoxShadowBR) var(--dfNodeBoxShadowS) var(--dfNodeBoxShadowColor);
  box-shadow: var(--dfNodeBoxShadowHL) var(--dfNodeBoxShadowVL)
    var(--dfNodeBoxShadowBR) var(--dfNodeBoxShadowS) var(--dfNodeBoxShadowColor);

  &.action {
    min-width: 3rem;
    text-align: center;

    &::before {
      content: "";
      @apply py-1 px-4 opacity-0 shadow-sm text-sm rounded-full bg-gray-200 transition absolute bottom-12 min-w-max;
    }
  }
}

.drawflow .drawflow-node:hover {
  background: var(--dfNodeHoverBackgroundColor);
  color: var(--dfNodeHoverTextColor);
  border: var(--dfNodeHoverBorderSize) solid var(--dfNodeHoverBorderColor);
  border-radius: var(--dfNodeHoverBorderRadius);
  -webkit-box-shadow: var(--dfNodeHoverBoxShadowHL)
    var(--dfNodeHoverBoxShadowVL) var(--dfNodeHoverBoxShadowBR)
    var(--dfNodeHoverBoxShadowS) var(--dfNodeHoverBoxShadowColor);
  box-shadow: var(--dfNodeHoverBoxShadowHL) var(--dfNodeHoverBoxShadowVL)
    var(--dfNodeHoverBoxShadowBR) var(--dfNodeHoverBoxShadowS)
    var(--dfNodeHoverBoxShadowColor);

  &.action::before {
    content: "right click to edit";
    @apply opacity-100;
  }
}

.drawflow .drawflow-node.selected {
  background: var(--dfNodeSelectedBackgroundColor);
  color: var(--dfNodeSelectedTextColor);
  border: var(--dfNodeSelectedBorderSize) solid var(--dfNodeSelectedBorderColor);
  border-radius: var(--dfNodeSelectedBorderRadius);
  -webkit-box-shadow: var(--dfNodeSelectedBoxShadowHL)
    var(--dfNodeSelectedBoxShadowVL) var(--dfNodeSelectedBoxShadowBR)
    var(--dfNodeSelectedBoxShadowS) var(--dfNodeSelectedBoxShadowColor);
  box-shadow: var(--dfNodeSelectedBoxShadowHL) var(--dfNodeSelectedBoxShadowVL)
    var(--dfNodeSelectedBoxShadowBR) var(--dfNodeSelectedBoxShadowS)
    var(--dfNodeSelectedBoxShadowColor);
}

.drawflow .drawflow-node .input {
  left: var(--dfInputLeft);
  background: var(--dfInputBackgroundColor);
  border: var(--dfInputBorderSize) solid var(--dfInputBorderColor);
  border-radius: var(--dfInputBorderRadius);
  height: var(--dfInputHeight);
  width: var(--dfInputWidth);
}

.drawflow .drawflow-node .input:hover {
  background: var(--dfInputHoverBackgroundColor);
  border: var(--dfInputHoverBorderSize) solid var(--dfInputHoverBorderColor);
  border-radius: var(--dfInputHoverBorderRadius);
}

.drawflow .drawflow-node .outputs {
  float: var(--dfNodeTypeFloat);
}

.drawflow .drawflow-node .output {
  right: var(--dfOutputRight);
  background: var(--dfOutputBackgroundColor);
  border: var(--dfOutputBorderSize) solid var(--dfOutputBorderColor);
  border-radius: var(--dfOutputBorderRadius);
  height: var(--dfOutputHeight);
  width: var(--dfOutputWidth);
}

.drawflow .drawflow-node .output:hover {
  background: var(--dfOutputHoverBackgroundColor);
  border: var(--dfOutputHoverBorderSize) solid var(--dfOutputHoverBorderColor);
  border-radius: var(--dfOutputHoverBorderRadius);
}

.drawflow .connection .main-path {
  stroke-width: var(--dfLineWidth);
  stroke: var(--dfLineColor);
}

.drawflow .connection .main-path:hover {
  stroke: var(--dfLineHoverColor);
}

.drawflow .connection .main-path.selected {
  stroke: var(--dfLineSelectedColor);
}

.drawflow .connection .point {
  stroke: var(--dfRerouteBorderColor);
  stroke-width: var(--dfRerouteBorderWidth);
  fill: var(--dfRerouteBackgroundColor);
}

.drawflow .connection .point:hover {
  stroke: var(--dfRerouteHoverBorderColor);
  stroke-width: var(--dfRerouteHoverBorderWidth);
  fill: var(--dfRerouteHoverBackgroundColor);
}

.drawflow-delete {
  display: var(--dfDeleteDisplay);
  color: var(--dfDeleteColor);
  background: var(--dfDeleteBackgroundColor);
  border: var(--dfDeleteBorderSize) solid var(--dfDeleteBorderColor);
  border-radius: var(--dfDeleteBorderRadius);
}

.parent-node .drawflow-delete {
  top: var(--dfDeleteTop);
}

.drawflow-delete:hover {
  color: var(--dfDeleteHoverColor);
  background: var(--dfDeleteHoverBackgroundColor);
  border: var(--dfDeleteHoverBorderSize) solid var(--dfDeleteHoverBorderColor);
  border-radius: var(--dfDeleteHoverBorderRadius);
}
</style>
