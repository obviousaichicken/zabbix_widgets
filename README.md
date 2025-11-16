These widgets have been tested on Zabbix 7.0.21, 7.2.14 and 7.4.5.

## Host overview

A widget for showing host metrics in a clean, compact way. You choose which metrics appear, set your own thresholds and adjust colors. Multi-item metrics are grouped neatly and values update live.

**Features**

- Choose which metrics are shown.
- Fields to define custom maximum load and interface values.
- Three levels of configurable threshold colors.
- Live updates for percentage and bitrate changes with tickers and horizontal animation.
- Multi-item metrics are laid out in uniform columns across the widget.
- Assigns shorthand labels for long interface names.
- Supports all Zabbix themes.

**Limitations**

This widget relies on predefined item-name patterns to detect which metrics to display. These patterns are based on the default item names in the official Windows and Linux templates that ship with Zabbix. The item names must match the schema below. If you are using the standard Linux and Windows templates version 7.0-2 or higher and have not renamed any items, you are compatible.

- "Load average (5m avg)"
- "Memory utilization"
- "CPU utilization"
- "**XXX**: Disk utilization"
- "FS [**XXX**]: Space: Used, in %"
- "Interface **XXX**: Bits received"
- "Interface **XXX**: Bits sent"

**Screenshots**

![](https://i.imgur.com/GspJmQF.gif)

![](https://i.imgur.com/znqWvZT.png)

![](https://i.imgur.com/sjFQwc6.png)

![](https://i.imgur.com/Py6JNi0.png)

## Banner

A widget for creating custom visual banners on your dashboard. Displays titles, descriptive text, and background images with lots of styling options. Perfect for highlighting key information, adding aesthetic elements or adding inline comments to your dashboard.

**Features**

- BB-codes.
- Add inline images.
- Horizontal text alignment.
- Seperate font colors and sizes for the title and description.
- Background color or image.
- Three background image display options.
- Header mode that centers the title and hides everything else.
- Supports all Zabbix themes.

**Screenshots**

![](https://i.imgur.com/8EoUFPU.png)

![](https://i.imgur.com/Nttk9na.png)

**Available BB-codes**

| BB-code                                     | Result                                                                                |
| ------------------------------------------- | ------------------------------------------------------------------------------------- |
| `[b]bold text[/b]`                          | **bold text**                                                                         |
| `[u]underlined[/u]`                         | underlined                                                                            |
| `[i]italic[/i]`                             | _italic_                                                                              |
| `[s]strike[/s]`                             | ~strike~                                                                              |
| `[center]centered text[/center]`            | centered text                                                                         |
| `[color=red]colored[/color]`                | colored text                                                                          |
| `[img]https://example.com/heart.png[/img]`  | ![image](https://icons.iconarchive.com/icons/paomedia/small-n-flat/16/heart-icon.png) |
| `[link=http://example.com]hyperlink[/link]` | [hyperlink](https://www.youtube.com/watch?v=dQw4w9WgXcQ)                              |
