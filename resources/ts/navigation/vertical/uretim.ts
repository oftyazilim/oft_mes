export default [
  {
    title: "Üretim",
    icon: { icon: "tabler-building-factory-2" },
    action: "read",
    subject: ["montaj", "mekanik"],
    children: [
      {
        title: "Montaj",
        to: "uretim-uretim-montaj",
        icon: { icon: "tabler-assembly" },
        action: "read",
        subject: ["montaj"],
        target: '_blank',
      },
      {
        title: "Rollform",
        to: "uretim-uretim-rollform",
        icon: { icon: "tabler-brand-bebo" },
        action: "read",
        subject: ["mekanik"],
        target: '_blank',
      },
    ],
  },
];
