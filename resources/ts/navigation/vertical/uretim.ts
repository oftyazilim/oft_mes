export default [
  {
    title: "Üretim",
    icon: { icon: "tabler-assembly" },
    action: "read",
    subject: "montaj",
    children: [
      {
        title: "Montaj",
        to: "uretim-uretim-montaj",
        icon: { icon: "tabler-assembly" },
        action: "read",
        subject: ["montaj"],
      },
    ],
  },
];
