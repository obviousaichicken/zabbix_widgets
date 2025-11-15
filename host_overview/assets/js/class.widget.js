/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

class CWidgetHostOverview extends CWidget {
  onInitialize() {
    this.rendered = false;
    // State for interface tickers
    this.interfaceTicker = new Map();
    // State for percent tickers
    this.percentTicker = new Map();
  }

  // Toggle a class on the container div if the header is hidden
  updateViewModeAttr() {
    try {
      if (!this._body) return;
      const container = this._body.querySelector("#container");
      if (!container) return;
      if (typeof this.getViewMode === "function") {
        const mode = this.getViewMode();
        container.classList.toggle("hidden_header", mode);
      }
    } catch (_) {}
  }

  // Set width and color for a fill element
  updateFillWidth(element, percent, fields = null) {
    if (!element) return;
    const p = Number(percent);
    const targetWidth = `${p}%`;
    element.style.width = targetWidth;

    // Apply color directly from config fields if available
    if (!fields) return;

    if (fields["color_scheme"] == "1") {
      element.style.backgroundColor = `#${fields["fill_color"]}`;
    } else {
      const highThreshold = Number(fields["th_num_1"]);
      const mediumThreshold = Number(fields["th_num_2"]);
      if (p > highThreshold) {
        element.style.backgroundColor = `#${fields["th_color_1"]}`;
      } else if (p > mediumThreshold) {
        element.style.backgroundColor = `#${fields["th_color_2"]}`;
      } else {
        element.style.backgroundColor = `#${fields["th_color_3"]}`;
      }
    }
  }

  // Widget lifecycle
  setContents(response) {
    // Render the skeleton once then update values
    if (!this.rendered) {
      super.setContents(response);
      this.rendered = true;
      // Ensure the container reflects the current view mode
      this.updateViewModeAttr();
    }

    // If config is present update values
    if (response.config && Object.keys(response.config).length > 0) {
      this.updateValues(response);
    }
  }

  // Update values in DOM
  updateValues(response) {
    if (!response || !response.config) return;

    // Keep container view mode in sync for styling
    this.updateViewModeAttr();

    const fields = response.config;

    // Helper for percent ticker
    const clampPercent = (n) => {
      const v = Math.round(Number(n) || 0);
      return Math.max(0, Math.min(100, v));
    };

    const startPercentTicker = (key, name, toPercent, textEl) => {
      const state = this.percentTicker.get(key) || { value: 0, rafId: null };
      if (state.rafId) cancelAnimationFrame(state.rafId);
      const from = Number(state.value) || 0;
      const to = clampPercent(toPercent);
      const start = performance.now();
      const dur = 500;
      const step = (now) => {
        const t = Math.min(1, (now - start) / dur);
        const ease = 1 - Math.pow(1 - t, 3);
        const cur = Math.round(from + (to - from) * ease);
        if (textEl) textEl.textContent = name ? `${name} — ${cur}%` : `${cur}%`;
        state.value = cur;
        if (t < 1) {
          state.rafId = requestAnimationFrame(step);
        } else {
          state.value = to;
          state.rafId = null;
          if (textEl) textEl.textContent = name ? `${name} — ${to}%` : `${to}%`;
        }
      };
      state.rafId = requestAnimationFrame(step);
      this.percentTicker.set(key, state);
    };

    // CPU (bar only, no percent text)
    if (fields["cpu_show"]) {
      const fill = this._body.querySelector(".cpu");
      const value = Number(response.cpu);
      if (fill && Number.isFinite(value)) {
        this.updateFillWidth(fill, value, fields);
      }
    }

    // RAM (bar only, no percent text)
    if (fields["ram_show"]) {
      const fill = this._body.querySelector(".ram");
      const value = Number(response.ram);
      if (fill && Number.isFinite(value)) {
        this.updateFillWidth(fill, value, fields);
      }
    }

    // Load
    if (fields["load_show"]) {
      const fill = this._body.querySelector(".load");
      const value = Number(response.load_percent);
      if (fill && Number.isFinite(value)) {
        this.updateFillWidth(fill, value, fields);
      }
    }

    // Helper to update a list of rows within a container
    const updateRows = (containerSelector, rows, keyPrefix, nameFormatter) => {
      const container = this._body.querySelector(containerSelector);
      if (!container || !rows || rows.length === 0) return;
      for (const row of rows) {
        const subcell = container.querySelector(
          `[data-key="${CSS.escape(row.name)}"]`
        );
        if (!subcell) continue;
        const text = subcell.querySelector(".text");
        const fill = subcell.querySelector(".fill");
        if (fill) this.updateFillWidth(fill, Number(row.percent), fields);
        if (text) {
          const labelName = nameFormatter ? nameFormatter(row) : row.name;
          startPercentTicker(
            `${keyPrefix}:${row.name}`,
            labelName,
            Number(row.percent),
            text
          );
        }
      }
    };

    // Interfaces ticker implementation
    const updateInterfaces = (interfaces) => {
      const container = this._body.querySelector(".interfaces-data");
      if (!container || !interfaces) return;

      const formatBits = (bps) => {
        const v = Math.max(0, Number(bps) || 0);
        if (v < 1_000_000) {
          let k = Math.floor(v / 1_000);
          if (k >= 1000) k = 999;
          return `${k} Kbps`;
        }
        if (v < 1_000_000_000) return `${(v / 1_000_000).toFixed(2)} Mbps`;
        return `${(v / 1_000_000_000).toFixed(2)} Gbps`;
      };

      const startTicker = (name, bps, percent) => {
        const key = name;
        const sub = container.querySelector(`[data-key="${CSS.escape(key)}"]`);
        if (!sub) return;
        const text = sub.querySelector(".text");
        const fill = sub.querySelector(".fill");

        const state = this.interfaceTicker.get(key) || {
          // Start from 0 on first render so bitrate ticks up
          value: 0,
          rafId: null,
        };
        if (state.rafId) cancelAnimationFrame(state.rafId);

        const from = Number(state.value) || 0;
        const to = Number(bps) || 0;
        const start = performance.now();
        const dur = 700;

        if (fill && Number.isFinite(Number(percent))) {
          this.updateFillWidth(fill, Number(percent), fields);
        }

        const label = String(name);
        const step = (now) => {
          const t = Math.min(1, (now - start) / dur);
          const ease = 1 - Math.pow(1 - t, 3);
          const cur = from + (to - from) * ease;
          if (text) text.textContent = `${label} — ${formatBits(cur)}`;
          state.value = cur;
          if (t < 1) {
            state.rafId = requestAnimationFrame(step);
          } else {
            state.value = to;
            state.rafId = null;
            if (text) text.textContent = `${label} — ${formatBits(to)}`;
          }
        };
        state.rafId = requestAnimationFrame(step);
        this.interfaceTicker.set(key, state);
      };

      for (const name in interfaces) {
        const item = interfaces[name] || {};
        startTicker(name, item.bps || 0, item.percent);
      }
    };

    // Interfaces (ticker)
    if (fields["interfaces_show"]) {
      updateInterfaces(response.interfaces);
    }

    // Disks
    if (fields["disks_show"]) {
      updateRows(".disks-data", response.disks, "disks", (row) => row.name);
    }

    // Partitions
    if (fields["partitions_show"]) {
      updateRows(
        ".partitions-data",
        response.partitions,
        "partitions",
        (row) => row.name
      );
    }
  }
}
