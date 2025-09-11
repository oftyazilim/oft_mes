export default [
  {
    title: "Depo",
    icon: { icon: "tabler-stack-2" },
    action: "read",
    subject: ["planlama", "depo", "satis", "satinalma", "montaj"],
    children: [
      {
        title: "Ürün Sorgula",
        to: "depo-urun-sorgula-trm",
        icon: { icon: "tabler-search" },
        action: "read",
        subject: ["terminal", 'depo'],
        target: '_blank',
      },
      {
        title: "Stoklar",
        to: "depo-stoklar",
        icon: { icon: "tabler-stack-2" },
        action: "read",
        subject: ["planlama", "depo", "satinalma", "satis", "montaj"],
        target: '_blank',
      },
    ],
  },
];

