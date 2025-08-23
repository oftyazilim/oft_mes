export default [
  {
    title: "Dinlence",
    icon: { icon: "tabler-smart-home" },
    action: "read",
    subject: "dashboard",
    children: [
      {
        title: "Gantt Test",
        to: "dinlence-test",
        icon: { icon: "tabler-list-tree" },
        action: "read",
        subject: "dashboard",
      },
      {
        title: "Adam Asmaca",
        to: "dinlence-adam-asmaca",
        icon: { icon: "tabler-device-gamepad-2" },
        action: "read",
        subject: "dashboard",
      },
      {
        title: "Yemek Mönüsü",
        to: "dinlence-yemek-listesi",
        action: "read",
        subject: ["Planlama", "Satınalma", "Satış", "Montaj"],
        target: "_blank",
      },
    ],
  },
];
