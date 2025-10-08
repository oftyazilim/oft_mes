export default [
  {
    title: "İstasyonlar",
    icon: { icon: "tabler-building-factory-2" },
    policyKey: "menu:istasyonlar",
    children: [
      {
        title: "Montaj",
        href: `${import.meta.env.BASE_URL}uretim/uretim-montaj`,
        icon: { icon: "tabler-assembly" },
        policyKey: "menu:istasyonlar:montaj",
        target: "oft-montaj",
      },
      {
        title: "Rollform",
        href: `${import.meta.env.BASE_URL}uretim/uretim-rollform`,
        icon: { icon: "tabler-brand-bebo" },
        policyKey: "menu:istasyonlar:rollform",
        target: "oft-rollform",
      },
    ],
  },
];
