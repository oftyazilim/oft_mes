export default [
  {
    title: "Depo",
    icon: { icon: "tabler-stack-2" },
    action: "read",
    subject: ["Planlama", "Satınalma", "Satış", "Montaj"],
    children: [
      {
        title: "Ürün Sorgula",
        to: "depo-urun-sorgula-trm",
        icon: { icon: "tabler-search" },
        action: "read",
        subject: ["Terminal"],
        target: '_blank',
      },
      {
        title: "Stoklar",
        to: "depo-stoklar",
        icon: { icon: "tabler-stack-2" },
        action: "read",
        subject: ["Planlama", "Satınalma", "Satış", "Montaj"],
        target: '_blank',
      },
    ],
  },
];

