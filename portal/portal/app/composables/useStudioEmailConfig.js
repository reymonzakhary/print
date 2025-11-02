export function useStudioEmailConfig() {
  const { t: $t } = useI18n();

  const fontFamilies = ref([
    "Arial",
    "Helvetica",
    "Verdana",
    "Tahoma",
    "Trebuchet MS",
    "Gill Sans",
    "Segoe UI",
    "Geneva",
    "Lucida Sans Unicode",
    "Lucida Grande",
    "Impact",
    "Times New Roman",
    "Georgia",
    "Palatino Linotype",
    "Book Antiqua",
    "Courier New",
    "Lucida Console",
    "Monaco",
    "Consolas",
    "Cambria",
  ]);

  // Studio configuration
  const studioConfig = ref([
    {
      id: "header",
      icon: "header",
      displayName: $t("Header"),
      fields: [
        {
          settingKey: "mail_header_bg_color",
          label: $t("Background"),
          type: "color",
          value: "#2f94b3",
        },
        {
          settingKey: "mail_header_alignment",
          label: $t("Alignment"),
          type: "radio",
          value: "left",
          options: [
            { value: "left", icon: "align-left", label: $t("Left") },
            { value: "center", icon: "align-center", label: $t("Center") },
            { value: "right", icon: "align-right", label: $t("Right") },
          ],
        },
        {
          type: "container",
          label: $t("Padding"),
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_header_padding.pt",
              label: $t("Top"),
              type: "input",
              inputType: "number",
              placeholder: $t("Top"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_header_padding.pr",
              label: $t("Right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Right"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_header_padding.pb",
              label: $t("Bottom"),
              type: "input",
              inputType: "number",
              placeholder: $t("Bottom"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_header_padding.pl",
              label: $t("Left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Left"),
              value: 0,
              min: 0,
            },
          ],
        },
      ],
    },
    {
      id: "logo",
      icon: "image",
      displayName: $t("Logo"),
      fields: [
        {
          settingKey: "mail_logo",
          label: $t("Logo Image URL:"),
          type: "image",
          value: null,
          withFetch: true,
        },
        {
          settingKey: "mail_logo_position",
          label: $t("Logo position"),
          type: "radio",
          value: "outside",
          options: [
            { value: "header", icon: "down-to-line", label: $t("Outside Header") },
            { value: "content", icon: "inbox-in", label: $t("Outside Content") },
            { value: "footer", icon: "up-to-line", label: $t("Inside Footer") },
          ],
        },
        {
          settingKey: "mail_align_logo",
          label: $t("Logo alignment"),
          type: "radio",
          value: "left",
          options: [
            { value: "left", icon: "align-left", label: $t("Left") },
            { value: "center", icon: "align-center", label: $t("Center") },
            { value: "right", icon: "align-right", label: $t("Right") },
          ],
        },
        {
          settingKey: "mail_logo_width",
          label: $t("Logo width (px or %)"),
          type: "input",
          inputType: "text",
          placeholder: $t("e.g., 150px or 20%"),
          value: "150px",
        },
      ],
    },
    {
      id: "theme",
      icon: "palette",
      displayName: $t("Email"),
      fields: [
        {
          settingKey: "mail_bg_color",
          label: $t("Background"),
          type: "color",
          value: "#ffffff",
        },
      ],
    },
    {
      id: "text",
      icon: "font",
      displayName: $t("Text"),
      fields: [
        {
          settingKey: "mail_fonts_color",
          label: $t("Font color"),
          type: "color",
          value: "#333333",
        },
        {
          settingKey: "mail_fonts_family",
          label: $t("Font family"),
          type: "select",
          value: "Arial, sans-serif",
          options: fontFamilies.value.map((font) => ({
            value: font,
            label: font,
          })),
        },
        {
          settingKey: "mail_fonts_size",
          label: $t("Font size (px)"),
          type: "select",
          options: [
            { value: "10px", label: "10px" },
            { value: "11px", label: "11px" },
            { value: "12px", label: "12px" },
            { value: "13px", label: "13px" },
            { value: "14px", label: "14px" },
            { value: "15px", label: "15px" },
            { value: "16px", label: "16px" },
            { value: "18px", label: "18px" },
            { value: "20px", label: "20px" },
            { value: "24px", label: "24px" },
          ],
        },
      ],
    },
    {
      id: "buttons",
      icon: "toggle-off",
      displayName: $t("Buttons"),
      fields: [
        {
          type: "container",
          label: $t("Primary colors"),
          class: "grid grid-cols-2 gap-2 items-end",
          children: [
            {
              settingKey: "mail_button_primary_colors",
              label: $t("Background"),
              type: "color",
              value: "#2f94b3",
            },
            {
              settingKey: "mail_text_button_primary_colors",
              label: $t("Text"),
              type: "color",
              value: "#ffffff",
            },
          ],
        },
        {
          type: "container",
          label: $t("Secondary colors"),
          class: "grid grid-cols-2 gap-2 items-end",
          children: [
            {
              settingKey: "mail_button_secondary_colors",
              label: $t("Background"),
              type: "color",
              value: "#333333",
            },
            {
              settingKey: "mail_text_button_secondary_colors",
              label: $t("Text"),
              type: "color",
              value: "#ffffff",
            },
          ],
        },
        {
          type: "container",
          label: $t("Border radius (px)"),
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_button_border_radius.tl",
              label: $t("Top left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Border radius"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_border_radius.tr",
              label: $t("Top right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Border radius"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_border_radius.br",
              label: $t("Bottom right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Border radius"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_border_radius.bl",
              label: $t("Bottom left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Border radius"),
              value: 0,
              min: 0,
            },
          ],
        },
        {
          type: "container",
          label: $t("Padding"),
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_button_padding.pt",
              label: $t("Top"),
              type: "input",
              inputType: "number",
              placeholder: $t("Top"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_padding.pr",
              label: $t("Right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Right"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_padding.pb",
              label: $t("Bottom"),
              type: "input",
              inputType: "number",
              placeholder: $t("Bottom"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_button_padding.pl",
              label: $t("Left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Left"),
              value: 0,
              min: 0,
            },
          ],
        },
        {
          settingKey: "mail_button_alignment",
          label: $t("Button alignment"),
          type: "radio",
          value: "left",
          options: [
            { value: "left", icon: "align-left", label: $t("Left") },
            { value: "center", icon: "align-center", label: $t("Center") },
            { value: "right", icon: "align-right", label: $t("Right") },
          ],
        },
      ],
    },
    {
      id: "content",
      icon: "window",
      displayName: $t("Content"),
      fields: [
        {
          settingKey: "mail_content_width",
          label: $t("Content width (px or %)"),
          type: "input",
          inputType: "text",
          placeholder: $t("e.g., 600px or 100%"),
          value: "600px",
        },
        {
          settingKey: "mail_content_bg_color",
          label: $t("Content background color"),
          type: "color",
          value: "#ffffff",
        },
        {
          settingKey: "mail_content_b_width",
          label: $t("Content border width (px)"),
          type: "input",
          inputType: "number",
          placeholder: $t("Border width"),
          value: 0,
          min: 0,
        },
        {
          settingKey: "mail_content_b_color",
          label: $t("Content border color"),
          type: "color",
          value: "#dddddd",
        },
        {
          label: $t("Border radius (px)"),
          type: "container",
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_content_b_radius.tl",
              label: $t("Top left"),
              type: "input",
              inputType: "number",
              placeholder: "0",
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_b_radius.tr",
              label: $t("Top right"),
              type: "input",
              inputType: "number",
              placeholder: "0",
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_b_radius.br",
              label: $t("Bottom right"),
              type: "input",
              inputType: "number",
              placeholder: "0",
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_b_radius.bl",
              label: $t("Bottom left"),
              type: "input",
              inputType: "number",
              placeholder: "0",
              value: 0,
              min: 0,
            },
          ],
        },
        {
          label: $t("Padding"),
          type: "container",
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_content_padding.pt",
              label: $t("Top"),
              type: "input",
              inputType: "number",
              placeholder: $t("Top"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_padding.pr",
              label: $t("Right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Right"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_padding.pb",
              label: $t("Bottom"),
              type: "input",
              inputType: "number",
              placeholder: $t("Bottom"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_content_padding.pl",
              label: $t("Left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Left"),
              value: 0,
              min: 0,
            },
          ],
        },
        {
          settingKey: "mail_content_alignment",
          label: $t("Content text alignment"),
          type: "radio",
          value: "left",
          options: [
            { value: "left", icon: "align-left", label: $t("Left") },
            { value: "center", icon: "align-center", label: $t("Center") },
            { value: "right", icon: "align-right", label: $t("Right") },
          ],
        },
      ],
    },
    {
      id: "footer",
      icon: "credit-card-blank",
      displayName: $t("Footer"),
      fields: [
        {
          settingKey: "mail_footer_alignment",
          label: $t("Footer text alignment"),
          type: "radio",
          value: "center",
          options: [
            { value: "left", icon: "align-left", label: $t("Left") },
            { value: "center", icon: "align-center", label: $t("Center") },
            { value: "right", icon: "align-right", label: $t("Right") },
          ],
        },
        {
          type: "container",
          label: $t("Colors"),
          class: "grid grid-cols-2 gap-2 items-end",
          children: [
            {
              settingKey: "mail_footer_bg_color",
              label: $t("Background"),
              type: "color",
              value: "#ffffff",
            },
            {
              settingKey: "mail_footer_fonts_color",
              label: $t("Text"),
              type: "color",
              value: "#888888",
            },
          ],
        },
        {
          settingKey: "mail_footer_fonts_size",
          label: $t("Footer font size (px)"),
          type: "select",
          value: "14px",
          options: [
            { value: "10px", label: "10px" },
            { value: "11px", label: "11px" },
            { value: "12px", label: "12px" },
            { value: "13px", label: "13px" },
            { value: "14px", label: "14px" },
            { value: "15px", label: "15px" },
            { value: "16px", label: "16px" },
            { value: "18px", label: "18px" },
            { value: "20px", label: "20px" },
            { value: "24px", label: "24px" },
          ],
        },
        {
          settingKey: "mail_footer_fonts_family",
          label: $t("Footer font family"),
          type: "select",
          value: "Arial, sans-serif",
          options: fontFamilies.value.map((font) => ({
            value: font,
            label: font,
          })),
        },
        {
          type: "container",
          label: $t("Padding"),
          class: "grid grid-cols-4 gap-2 items-end",
          children: [
            {
              settingKey: "mail_footer_padding.pt",
              label: $t("Top"),
              type: "input",
              inputType: "number",
              placeholder: $t("Top"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_footer_padding.pr",
              label: $t("Right"),
              type: "input",
              inputType: "number",
              placeholder: $t("Right"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_footer_padding.pb",
              label: $t("Bottom"),
              type: "input",
              inputType: "number",
              placeholder: $t("Bottom"),
              value: 0,
              min: 0,
            },
            {
              settingKey: "mail_footer_padding.pl",
              label: $t("Left"),
              type: "input",
              inputType: "number",
              placeholder: $t("Left"),
              value: 0,
              min: 0,
            },
          ],
        },
      ],
    },
  ]);

  // useStudioSettings

  // Email Config Values
  const { getPreview } = useLoadImage();
  const getLogo = (logoField) => getPreview(logoField);

  const getEmailConfig = (getFieldValue) => {
    // Create a separate computed for the logo image that only updates when mail_logo changes
    const logoImage = computed(() => {
      const logoFieldValue = getFieldValue("mail_logo").value;
      return logoFieldValue ? getLogo(logoFieldValue).blob : null;
    });

    // Return the computed config
    return computed(() => ({
      header: {
        backgroundColor: getFieldValue("mail_header_bg_color").value,
        alignment: getFieldValue("mail_header_alignment").value,
        padding: {
          pt: getFieldValue("mail_header_padding.pt").value,
          pr: getFieldValue("mail_header_padding.pr").value,
          pb: getFieldValue("mail_header_padding.pb").value,
          pl: getFieldValue("mail_header_padding.pl").value,
        },
      },
      theme: {
        backgroundColor: getFieldValue("mail_bg_color").value,
        primaryColor: getFieldValue("mail_button_primary_colors").value,
        primaryTextColor: getFieldValue("mail_text_button_primary_colors").value,
        secondaryColor: getFieldValue("mail_button_secondary_colors").value,
        secondaryTextColor: getFieldValue("mail_text_button_secondary_colors").value,
      },
      text: {
        fontColor: getFieldValue("mail_fonts_color").value,
        fontSize: getFieldValue("mail_fonts_size").value,
        fontFamily: getFieldValue("mail_fonts_family").value,
      },
      buttons: {
        borderRadius: {
          tr:
            getFieldValue("mail_button_border_radius.tr").value ??
            getFieldValue("mail_button_border_radius.bt").value,
          tl:
            getFieldValue("mail_button_border_radius.tl").value ??
            getFieldValue("mail_button_border_radius.bb").value,
          br: getFieldValue("mail_button_border_radius.br").value,
          bl: getFieldValue("mail_button_border_radius.bl").value,
        },
        padding: {
          pt: getFieldValue("mail_button_padding.pt").value,
          pr: getFieldValue("mail_button_padding.pr").value,
          pb: getFieldValue("mail_button_padding.pb").value,
          pl: getFieldValue("mail_button_padding.pl").value,
        },
        alignment: getFieldValue("mail_button_alignment").value,
      },
      content: {
        width: getFieldValue("mail_content_width").value,
        backgroundColor: getFieldValue("mail_content_bg_color").value,
        borderWidth: getFieldValue("mail_content_b_width").value,
        borderColor: getFieldValue("mail_content_b_color").value,
        borderRadius: {
          tl: getFieldValue("mail_content_b_radius.tl").value,
          tr: getFieldValue("mail_content_b_radius.tr").value,
          br: getFieldValue("mail_content_b_radius.br").value,
          bl: getFieldValue("mail_content_b_radius.bl").value,
        },
        padding: {
          pt: getFieldValue("mail_content_padding.pt").value,
          pr: getFieldValue("mail_content_padding.pr").value,
          pb: getFieldValue("mail_content_padding.pb").value,
          pl: getFieldValue("mail_content_padding.pl").value,
        },
        alignment: getFieldValue("mail_content_alignment").value,
      },
      logo: {
        image: logoImage.value,
        position: getFieldValue("mail_logo_position").value,
        alignment: getFieldValue("mail_align_logo").value,
        width: getFieldValue("mail_logo_width").value,
      },
      footer: {
        alignment: getFieldValue("mail_footer_alignment").value,
        backgroundColor: getFieldValue("mail_footer_bg_color").value,
        fontColor: getFieldValue("mail_footer_fonts_color").value,
        fontSize: getFieldValue("mail_footer_fonts_size").value,
        fontFamily: getFieldValue("mail_footer_fonts_family").value,
        padding: {
          pt: getFieldValue("mail_footer_padding.pt").value,
          pr: getFieldValue("mail_footer_padding.pr").value,
          pb: getFieldValue("mail_footer_padding.pb").value,
          pl: getFieldValue("mail_footer_padding.pl").value,
        },
      },
    }));
  };

  /**
   * Make sure to also add the tags for the Order & Invoice mail templates
   */
  const quotationTags = reactive([
    {
      name: "[[%quotation.id]]",
      display: $t("ID"),
      description: $t("Unique identifier for this quotation"),
    },
    {
      name: "[[%quotation.reference]]",
      display: $t("Reference"),
      description: $t("Reference number"),
    },
    { name: "[[%quotation.price]]", display: $t("Price"), description: $t("Total price") },
    {
      name: "[[%quotation.expire_at]]",
      display: $t("Expiry Date"),
      description: $t("Expiration date"),
    },
    {
      name: "[[%quotation.status.name]]",
      display: $t("Status Name"),
      description: $t("Current status name"),
    },
    {
      name: "[[%quotation.status.code]]",
      display: $t("Status Code"),
      description: $t("Current status code"),
    },
    {
      name: "[[%quotation.delivery_address]]",
      display: $t("Delivery Address"),
      description: $t("Address for delivery"),
    },
    {
      name: "[[%quotation.invoice_address]]",
      display: $t("Invoice Address"),
      description: $t("Address for invoice"),
    },
    {
      name: "[[%quotation.context]]",
      display: $t("Context"),
      description: $t("Additional context"),
    },
    {
      name: "[[%quotation.vat_price]]",
      display: $t("VAT Amount"),
      description: $t("VAT amount"),
    },
    {
      name: "[[%quotation.created_from]]",
      display: $t("Created From"),
      description: $t("Origin of creation"),
    },
    {
      name: "[[%customer.full_name]]",
      display: $t("Full Name"),
      description: $t("Customer's full name"),
    },
    {
      name: "[[%customer.first_name]]",
      display: $t("First Name"),
      description: $t("Customer's first name"),
    },
    {
      name: "[[%customer.last_name]]",
      display: $t("Last Name"),
      description: $t("Customer's last name"),
    },
    {
      name: "[[%customer.email]]",
      display: $t("Email"),
      description: $t("Customer's email address"),
    },
    { name: "[[%customer.gender]]", display: $t("Gender"), description: $t("Customer's gender") },
  ]);

  return { studioConfig, getEmailConfig, quotationTags };
}
