/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

window.form = new (class {
  // Init color pickers and field dependencies
  init(options) {
    if (options && options.color_picker_class) {
      this.initColorPickers(options.color_picker_class);
    }

    this.initFieldDependencies();
  }

  // Attach colorpicker to all color inputs
  initColorPickers(colorPickerClass) {
    const selector = `.${colorPickerClass} input`;

    for (const colorpicker of jQuery(selector)) {
      jQuery(colorpicker).colorpicker();
    }

    // Close colorpickers when the widget properties overlay changes
    const overlay = overlays_stack.getById("widget_properties");

    if (!overlay || !overlay.$dialogue || !overlay.$dialogue[0]) {
      return;
    }

    for (const event of ["overlay.reload", "overlay.close"]) {
      overlay.$dialogue[0].addEventListener(event, () => {
        jQuery.colorpicker("hide");
      });
    }
  }

  // Init checkbox / radio dependency groups
  initFieldDependencies() {
    this.initToggleGroup({
      showId: "interfaces_show",
      checkId: "interfaces_high",
      radiosContainerId: "interfaces_unit",
    });

    this.initToggleGroup({
      showId: "load_show",
      checkId: "load_high",
    });
  }

  // Link master checkbox, optional checkbox and radios
  initToggleGroup({ showId, checkId, radiosContainerId }) {
    const show = document.getElementById(showId);

    if (!show) {
      return;
    }

    const check = checkId ? document.getElementById(checkId) : null;
    const radiosContainer = radiosContainerId
      ? document.getElementById(radiosContainerId)
      : null;
    const radios = radiosContainer
      ? radiosContainer.querySelectorAll('input[type="radio"]')
      : null;

    // Enable/disable radios without changing checked state
    const setRadiosEnabled = (enabled) => {
      if (!radios) {
        return;
      }

      radios.forEach((radio) => {
        radio.disabled = !enabled;
      });
    };

    const update = () => {
      const enabled = !!show.checked;

      if (check) {
        check.disabled = !enabled;

        if (!enabled) {
          check.checked = false;
        }
      }

      setRadiosEnabled(enabled);
    };

    show.addEventListener("change", update);
    update();
  }
})();
