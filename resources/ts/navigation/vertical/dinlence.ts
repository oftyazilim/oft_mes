export default [
  {
    title: "Dinlence",
    icon: { icon: "tabler-smart-home" },
    action: "read",
    subject: "genel",
    children: [
      {
        title: "Gantt Test",
        to: "dinlence-test",
        icon: { icon: "tabler-list-tree" },
        action: "read",
        subject: "genel",
        target: "_blank",
      },
      {
        title: "Adam Asmaca",
        to: "dinlence-adam-asmaca",
        icon: { icon: "tabler-device-gamepad-2" },
        action: "read",
        subject: "genel",
        target: "_blank",
      },
      {
        title: "Yemek Mönüsü",
        to: "dinlence-yemek-listesi",
        action: "read",
        subject: ["genel"],
        target: "_blank",
      },
    ],
  },
];
