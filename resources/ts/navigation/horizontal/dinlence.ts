export default [
  {
    title: "Dinlence",
    icon: { icon: "tabler-smart-home" },
    action: "manage",
    subject: "all",
    children: [
      {
        title: "Gantt Test",
        to: "dinlence-test",
        icon: { icon: "tabler-list-tree" },
        action: "manage",
        subject: "all",
        target: "_blank",
      },
      {
        title: "Ağaç Test",
        to: "dinlence-agac-test",
        icon: { icon: "tabler-list-tree" },
        action: "manage",
        subject: "all",
        target: "_blank",
      },
      {
        title: "Adam Asmaca",
        to: "dinlence-adam-asmaca",
        icon: { icon: "tabler-device-gamepad-2" },
        action: "manage",
        subject: "all",
        target: "_blank",
      },
      {
        title: "Yemek Mönüsü",
        to: "dinlence-yemek-listesi",
        action: "manage",
        subject: ["all"],
        target: "_blank",
      },
    ],
  },
];
