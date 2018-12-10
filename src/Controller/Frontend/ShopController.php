<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Commerce\GP;
use GSS\Component\Commerce\Shop;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Shop.
 */
class ShopController extends Controller
{
    private const UPGRADES = [
        'ftp' => [
            'field' => 'MaxFTP',
            'value' => '100',
            'default' => 1,
        ],
        'mysql' => [
            'field' => 'MaxMySQL',
            'value' => '100',
            'default' => 1,
        ],
        'gast' => [
            'field' => 'MaxGast',
            'value' => '200',
            'default' => 1,
        ],
        'server' => [
            'field' => 'MaxServer',
            'value' => '500',
            'default' => 1,
        ],
        'slots' => [
            'field' => 'MaxSlots',
            'value' => '0',
            'default' => 5,
        ],
    ];

    /**
     * @Route("/shop")
     * @Route("/shop/")
     * @Route("/shop/index/{type}")
     * @Route("/shop/index/{type}/{upgrades}")
     * Index Action.
     *
     * @param string $type
     * @param string $upgrades
     *
     * @return string
     */
    public function indexAction($type = 'active', $upgrades = '')
    {
        $this->View()->setPageTitle('Server');

        if ($type === 'upgrades') {
            $this->data['upgrades'] = [];

            foreach (self::UPGRADES as $key => $upgrade) {
                $this->data['upgrades'][$key] = $upgrade['value'] + ($this->container->get('session')->getUserData($upgrade['field']) * 50);
            }

            if (!empty(self::UPGRADES[$upgrades])) {
                if ($this->container->get('session')->getUserData('GP') >= $this->data['upgrades'][$upgrades]) {
                    $userID = $this->container->get('session')->getUserID();

                    $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users SET RankPoints = RankPoints + 1 WHERE id = ?', [$userID]);

                    $this->container->get(GP::class)->removePointsFromUser(
                        $userID,
                        $this->data['upgrades'][$upgrades],
                        \ucfirst($upgrades) . ' ' . __('Upgrade gekauft', 'Shop', 'UpgradeBuyed')
                    );

                    $this->container->get('app.user.user')->setData(
                        $userID,
                        self::UPGRADES[$upgrades]['field'],
                        $this->container->get('session')->getUserData(self::UPGRADES[$upgrades]['field']) + 1
                    );
                    $this->container->get('session')->flashMessenger()->addSuccess(
                        'Upgrade',
                        \ucfirst($upgrades) . ' ' . __('Upgrade gekauft', 'Shop', 'UpgradeBuyed')
                    );

                    if ($upgrades === 'slots') {
                        $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE gameserver SET Slot = ? WHERE Typ = 1 AND userID = ?', [
                            $this->container->get('session')->get('user/MaxSlots') + 1,
                            $this->userID,
                        ]);
                    }

                    return $this->redirectToRoute('gss_frontend_shop_index', ['type' => 'upgrades']);
                }

                $this->container->get('session')->flashMessenger()->addError(
                    'Upgrade',
                    __('Deine GP Punkte reichen nicht für dieses Upgrade aus', 'Shop', 'UpgradeGP')
                );
            }
        }

        /*
         * When user tries a invalid tab
         */
        if (!($type === 'active' || $type === 'passive' || $type === 'upgrades')) {
            $type = 'active';
        }

        $this->data['mode'] = $type;

        $this->data['breadcrumb'] = [
            [
                'name' => 'Shop',
            ],
        ];

        if ($type === 'active') {
            $this->data['activeServers'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
				SELECT
				*,
				(SELECT gp FROM products_sub WHERE type = 0 AND active = 1 AND productID = products.id ORDER BY gp ASC LIMIT 1) AS minimumGP
				FROM products
				WHERE (SELECT COUNT(*) FROM products_sub WHERE type = 0 AND active = 1 AND productID = products.id) > 0 and products.active = 1
			');

            $this->data['breadcrumb'][] = [
                'name' => __('Aktive Server', 'Shop', 'Activeserver'),
            ];
        } elseif ($type === 'passive') {
            $this->data['passiveServers'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
				SELECT
				*,
				(SELECT gp FROM products_sub WHERE type = 1 AND active = 1 AND productID = products.id ORDER BY gp ASC LIMIT 1) AS minimumGP,
				(SELECT id FROM products_sub WHERE type = 1 AND active = 1 AND productID = products.id ORDER BY gp ASC LIMIT 1) AS minimumGPID
				FROM products WHERE (SELECT COUNT(*) FROM products_sub WHERE type = 1 AND active = 1 AND productID = products.id) > 0 AND products.active = 1
			');

            $this->data['breadcrumb'][] = [
                'name' => __('Passive Server', 'Shop', 'Passiveserver'),
            ];
        } else {
            $this->data['breadcrumb'][] = [
                'name' => __('Upgrades', 'Shop', 'Upgrades'),
            ];
        }

        return $this->render('frontend/shop/index.twig', $this->data);
    }

    /**
     * @Route("/shop/showVariants/{type}/{gameInternalName}")
     * Variant Action.
     *
     * @param $type
     * @param $gameInternalName
     *
     * @return string
     */
    public function showVariantsAction($type, $gameInternalName)
    {
        $this->View()->setPageTitle('Server Variante wählen');

        $this->data['variants'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT
          products_sub.*,
          products.name,
          products.description_de,
          products.description_en,
          products.img
        FROM
          products_sub
        LEFT JOIN products ON(products.id = products_sub.productID)
        WHERE productID = (SELECT ID FROM products WHERE internalName = ? LIMIT 1)
          AND type = ? 
        ORDER BY gp ASC', [$gameInternalName, $type]);

        if (empty($this->data['variants'])) {
            return $this->redirectToRoute('gss_frontend_shop_index');
        }

        $this->data['breadcrumb'] = [
            [
                'name' => 'Shop',
                'link' => $this->generateUrl('gss_frontend_shop_index'),
            ],
            [
                'name' => $this->data['variants'][0]['name'],
            ],
        ];

        return $this->render('frontend/shop/showVariants.twig', $this->data);
    }

    /**
     * @Route("/shop/buyPackage/{packageId}")
     * Buy Package.
     *
     * @param $packageId
     *
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function buyPackageAction($packageId)
    {
        $this->View()->setPageTitle('Server Kaufen');

        $this->data['variant'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('
			SELECT * FROM products_sub
			LEFT JOIN products ON(products.id = products_sub.productID)
			WHERE products_sub.id = ?', [$packageId]);

        if (empty($this->data['variant'])) {
            return $this->redirectToRoute('gss_frontend_shop_index');
        }

        $this->data['breadcrumb'] = [
            [
                'name' => 'Shop',
                'link' => $this->generateUrl('gss_frontend_shop_index'),
            ],
            [
                'name' => $this->data['variant']['name'],
                'link' => $this->data['variant']['type'] === 0 ? $this->generateUrl('gss_frontend_shop_showvariants', [
                    'type' => $this->data['variant']['type'],
                    'gameInternalName' => $this->data['variant']['internalName'],
                ]) : '#',
            ],
            [
                'name' => $this->data['variant']['slots'] . ' Slots',
                'link' => '',
            ],
        ];

        /*
         * Customer has used Buy
         */
        if ($this->Request()->request->count()) {
            $this->container->get('doctrine.dbal.default_connection')->executeQuery(
                'UPDATE users SET RankPoints = RankPoints + 1 WHERE ID = ?',
                [$this->container->get('session')->getUserID()]
            );
            $this->container->get(Shop::class)->buyNewServer($packageId);
        }

        return $this->render('frontend/shop/buy.twig', $this->data);
    }
}
