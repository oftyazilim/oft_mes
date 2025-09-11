export default [
  {
    title: "Satış",
    icon: { icon: "tabler-calendar-dollar" },
    action: "read",
    subject: ["satis", 'planlama'],
    children: [
      {
        title: "Müşteri Siparişleri",
        to: "satis-musteri-siparisleri",
        action: "read",
        icon: { icon: "tabler-list" },
        subject: ["satis", 'planlama'],
        target: "_blank",
      },
      {
        title: "Ciro Özet Tablo",
        to: "satis-ciro-ozettablo",
        action: "read",
        icon: { icon: "tabler-table" },
        subject: ["satis", 'planlama'],
        target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satis-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["satis", 'planlama'],
        target: '_blank',
      },
    ],
  },
];

