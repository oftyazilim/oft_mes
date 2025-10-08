export default [
  {
    title: "Depo",
    icon: { icon: "tabler-stack-2" },
    policyKey: "menu:depo",
    children: [
      {
        title: "Ürün Sorgula",
        to: "depo-urun-sorgula-trm",
        icon: { icon: "tabler-search" },
        policyKey: "menu:depo:urun-sorgula",
        target: "_blank",
      },
      {
        title: "Stoklar",
        to: "depo-stoklar",
        icon: { icon: "tabler-stack-2" },
        policyKey: "menu:depo:stoklar",
        target: "_blank",
      },
    ],
  },
];

