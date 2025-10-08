export default [
  {
    title: "Satınalma",
    icon: { icon: "tabler-shopping-cart" },
    policyKey: "menu:satinalma",
    children: [
      {
        title: "Satınalma Siparişleri",
        to: "satinalma-satinalma-siparisleri",
        icon: { icon: "tabler-list" },
        policyKey: "menu:satinalma:siparisler",
        target: "_blank",
      },
      {
        title: "Satınalma Talepleri",
        to: "satinalma-satinalma-talepler",
        icon: { icon: "tabler-list" },
        policyKey: "menu:satinalma:talepler",
        target: "_blank",
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satinalma-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        policyKey: "menu:satinalma:ihtiyac-listesi-siparis",
        target: "_blank",
      },
    ],
  },
];
