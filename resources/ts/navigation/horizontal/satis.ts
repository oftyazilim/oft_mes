export default [
  {
    title: "Satış",
    icon: { icon: "tabler-calendar-dollar" },
    policyKey: "menu:satis",
    children: [
      {
        title: "Müşteri Siparişleri",
        to: "satis-musteri-siparisleri",
        icon: { icon: "tabler-list" },
        policyKey: "menu:satis:musteri-siparisleri",
        target: "_blank",
      },
      {
        title: "Ciro Özet Tablo",
        to: "satis-ciro-ozettablo",
        icon: { icon: "tabler-table" },
        policyKey: "menu:satis:ciro-ozet",
        target: "_blank",
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satis-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        policyKey: "menu:satis:ihtiyac-listesi-siparis",
        target: "_blank",
      },
    ],
  },
];

