export default [
  {
    title: "Üretim",
    icon: { icon: "tabler-building-factory-2" },
    action: "read",
    subject: ["montaj", "mekanik"],
    children: [
      {
        title: "Montaj",
        // RouterLink yerine anchor ile adlandırılmış pencere hedefi kullan
        href: `${import.meta.env.BASE_URL}uretim/uretim-montaj`,
        icon: { icon: "tabler-assembly" },
        action: "read",
        subject: ["montaj"],
        target: "oft-montaj",
      },
      {
        title: "Rollform",
        href: `${import.meta.env.BASE_URL}uretim/uretim-rollform`,
        icon: { icon: "tabler-brand-bebo" },
        action: "read",
        subject: ["mekanik"],
        target: "oft-rollform",
      },
    ],
  },
];
