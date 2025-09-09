export default [
  {
    title: "Satış",
    icon: { icon: "tabler-calendar-dollar" },
    action: "read",
    subject: ["users"],
    children: [
      {
        title: "Müşteri Siparişleri",
        to: "satis-musteri-siparisleri",
        action: "read",
        icon: { icon: "tabler-list" },
        subject: ["users"],
        target: "_blank",
      },
      {
        title: "Ciro Özet Tablo",
        to: "satis-ciro-ozettablo",
        action: "read",
        icon: { icon: "tabler-table" },
        subject: ["Planlama"],
        target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satis-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["Satış", "Planlama"],
        target: '_blank',
      },
    ],
  },
];

