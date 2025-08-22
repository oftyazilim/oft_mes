export default [
  // { heading: "KALİTE", action: "read", subject: "Dashboard" },
  {
    title: "Kalite",
    icon: { icon: "tabler-microscope" },
    action: "read",
    subject: ["Kalite"],
    children: [
      {
        title: "Ürün Kontrolü",
        to: "kalite-uretim-kontrol",
        icon: { icon: "tabler-microscope" },
        action: "read",
        subject: ["Kalite"],
        // target: '_blank',
      },
      {
        title: "Kontrol Listeleri",
        to: "kalite-kontroller",
        icon: { icon: "tabler-microscope" },
        action: "read",
        subject: ["Kalite"],
        // target: '_blank',
      },
    ],
  },
];
