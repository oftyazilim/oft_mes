export default [
  // { heading: "KALİTE", action: "read", subject: "Dashboard" },
  {
    title: "Kalite",
    icon: { icon: "tabler-microscope" },
    policyKey: "menu:kalite",
    children: [
      {
        title: "Ürün Kontrolü",
        to: "kalite-uretim-kontrol",
        icon: { icon: "tabler-microscope" },
        policyKey: "menu:kalite:uretim-kontrol",
        target: "_blank",
      },
      {
        title: "Kontrol Listeleri",
        to: "kalite-kontroller",
        icon: { icon: "tabler-microscope" },
        policyKey: "menu:kalite:kontroller",
        target: "_blank",
      },
    ],
  },
];
