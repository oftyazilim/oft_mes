export default [
  {
    title: "Satınalma",
    icon: { icon: "tabler-shopping-cart" },
    action: "read",
    subject: ["satinalma", "planlama"],
    children: [
      {
        title: "Satınalma Siparişleri",
        to: "satinalma-satinalma-siparisleri",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["satinalma", "planlama"],
        // target: '_blank',
      },
      {
        title: "Satınalma Talepleri",
        to: "satinalma-satinalma-talepler",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["satinalma", "planlama"],
        // target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satinalma-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["satinalma", "planlama"],
        // target: '_blank',
      },
    ],
  },
];
