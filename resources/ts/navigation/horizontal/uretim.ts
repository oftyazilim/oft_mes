export default [
  {
    title: "Üretim",
    icon: { icon: "tabler-automation" },
    policyKey: "menu:uretim",
    children: [
      {
        title: "Duruşlar (Montaj)",
        href: `${import.meta.env.BASE_URL}uretim/duruslar-montaj`,
        icon: { icon: "tabler-clock-cog" },
        policyKey: "menu:uretim:duruslar-montaj",
        target: "_blank",
      },
      {
        title: "Duruşlar (Mekanik)",
        href: `${import.meta.env.BASE_URL}uretim/duruslar-mekanik`,
        icon: { icon: "tabler-clock-cog" },
        policyKey: "menu:uretim:duruslar-mekanik",
        target: "_blank",
      },
    ],
  },
];
