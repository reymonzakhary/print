// @vitest-environment happy-dom

import { mount } from "@vue/test-utils";
import UIButton from "./UIButton.vue";
import { describe, it, expect } from "vitest";

const FontAwesomeIcon = {
  name: "FontAwesomeIcon",
  props: ["icon"],
  template: "<span>{{ icon }}</span>",
};

describe("UIButton", () => {
  const global = {
    global: {
      components: {
        FontAwesomeIcon,
      },
    },
  };

  it("renders the button with default slot content", () => {
    const wrapper = mount(UIButton, {
      ...global,
      slots: {
        default: "Click me",
      },
    });
    expect(wrapper.text()).toBe("Click me");
  });

  it("renders the icon when provided", () => {
    const wrapper = mount(UIButton, {
      ...global,
      props: {
        icon: ["fal", "user"],
      },
    });
    expect(wrapper.findComponent(FontAwesomeIcon).exists()).toBe(true);
  });

  it("applies square class when no default slot content", () => {
    const wrapper = mount(UIButton, global);
    expect(wrapper.classes()).toContain("aspect-square");
  });

  it("emits click event when clicked", async () => {
    const wrapper = mount(UIButton, global);
    await wrapper.trigger("click");
    expect(wrapper.emitted()).toHaveProperty("click");
  });
});
