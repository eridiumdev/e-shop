<?php
namespace App\Test\Data;

require_once __DIR__ . '/../../web/config.php';

use PHPUnit\Framework\TestCase;

use App\Model\Data\User;
use App\Model\Data\Shipping;
use App\Model\Data\Order;
use App\Model\Data\OrderItem;
use App\Model\Data\Delivery;
use App\Model\Data\Payment;
use App\Model\Data\Category;
use App\Model\Data\Spec;
use App\Model\Data\Product;
use App\Model\Data\Section;
use App\Model\Data\Picture;
use App\Model\Data\Discount;

class DataTest extends TestCase
{
    public function runSetter(&$obj, &$props)
    {
        foreach ($props as $propName => $propValue) {
            $setter = 'set' . ucfirst($propName);
            $obj->$setter($propValue);
        }
    }

    public function runDataTest($obj, $props)
    {
        foreach ($props as $propName => $propValue) {
            $getter = 'get' . ucfirst($propName);
            $this->assertEquals(
                $propValue,
                $obj->$getter(),
                "Problem found during $getter test."
            );
        }
    }

    /**
     * @test
     */
    public function UserClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getUserData();
        $user = new User();

        $shippingData = $dataProvider->getShippingData();
        $shipping = new Shipping();
        $this->runSetter($shipping, $shippingData);
        $data['shipping'] = $shipping;

        $ordersData = $dataProvider->getOrdersData();
        foreach ($ordersData as $orderData) {
            $order = new Order();
            $this->runSetter($order, $orderData);
            $data['orders'][] = $order;
        }

        $this->runSetter($user, $data);
        $this->runDataTest($user, $data);
    }

    /**
     * @test
     */
    public function ShippingClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getShippingData();
        $shipping = new Shipping();

        $this->runSetter($shipping, $data);
        $this->runDataTest($shipping, $data);
    }

    /**
     * @test
     */
    public function OrderClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getOrdersData()[0];
        $order = new Order();

        $userData = $dataProvider->getUserData();
        $user = new User();
        $this->runSetter($user, $userData);
        $data['user'] = $user;

        $deliveryData = $dataProvider->getDeliveryData();
        $delivery = new Delivery();
        $this->runSetter($delivery, $deliveryData);
        $data['delivery'] = $delivery;

        $paymentData = $dataProvider->getPaymentData();
        $payment = new Payment();
        $this->runSetter($payment, $paymentData);
        $data['payment'] = $payment;

        $itemsData = $dataProvider->getOrderItemsData();
        foreach ($itemsData as $itemData) {
            $item = new OrderItem();
            $this->runSetter($item, $itemData);
            $data['items'][] = $item;
        }

        $this->runSetter($order, $data);
        $this->runDataTest($order, $data);

        $this->assertEquals(750, $order->getTotal());
    }

    /**
     * @test
     */
    public function DeliveryClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getDeliveryData();
        $delivery = new Delivery();

        $this->runSetter($delivery, $data);
        $this->runDataTest($delivery, $data);
    }

    /**
     * @test
     */
    public function PaymentClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getPaymentData();
        $payment = new Payment();

        $this->runSetter($payment, $data);
        $this->runDataTest($payment, $data);
    }

    /**
     * @test
     */
    public function CategoryClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getCategoriesData()[0];
        $category = new Category();

        $specsData = $dataProvider->getSpecsData();
        foreach ($specsData as $specData) {
            $spec = new Spec();
            $this->runSetter($spec, $specData);
            $data['specs'][] = $spec;
        }

        $productsData = $dataProvider->getProductsData();
        foreach ($productsData as $productData) {
            $product = new Product();
            $this->runSetter($product, $productData);
            $data['products'][] = $product;
        }

        $this->runSetter($category, $data);
        $this->runDataTest($category, $data);
    }

    /**
     * @test
     */
    public function SpecClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getSpecsData()[0];
        $spec = new Spec();

        $catsData = $dataProvider->getCategoriesData();
        foreach ($catsData as $catData) {
            $category = new Category();
            $this->runSetter($category, $catData);
            $data['categories'][] = $category;
        }

        $this->runSetter($spec, $data);
        $this->runDataTest($spec, $data);
    }

    /**
     * @test
     */
    public function ProductClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getProductsData()[0];
        $product = new Product();

        $categoryData = $dataProvider->getCategoriesData()[0];
        $category = new Category();
        $this->runSetter($category, $categoryData);
        $data['category'] = $category;

        $picsData = $dataProvider->getPicsData();
        $mainPic = true;
        foreach ($picsData as $picData) {
            $pic = new Picture();
            $this->runSetter($pic, $picData);
            if ($mainPic) {
                $data['mainPic'] = $pic;
                $mainPic = false;
            } else {
                $data['pics'][] = $pic;
            }
        }

        $discountData = $dataProvider->getDiscountData();
        $discount = new Discount();
        $this->runSetter($discount, $discountData);
        $data['discount'] = $discount;

        $specsData = $dataProvider->getSpecsData();
        foreach ($specsData as $specData) {
            $spec = new Spec();
            $this->runSetter($spec, $specData);
            $data['specs'][] = $spec;
        }

        $this->runSetter($product, $data);
        $this->runDataTest($product, $data);

        $product->setPrice(1000);
        $product->setDiscount(new Discount(0.4));
        $this->assertEquals(400, $product->getDiscountedPrice());
    }

    /**
     * @test
     */
    public function PictureClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getPicsData()[0];
        $picture = new Picture();

        $this->runSetter($picture, $data);
        $this->runDataTest($picture, $data);
    }

    /**
     * @test
     */
    public function DiscountClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getDiscountData();
        $discount = new Discount();

        $this->runSetter($discount, $data);
        $this->runDataTest($discount, $data);
    }

    /**
     * @test
     */
    public function OrderItemClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getOrderItemsData()[0];
        $item = new OrderItem();

        $this->runSetter($item, $data);
        $this->runDataTest($item, $data);

        $item->setPrice(1000);
        $item->setDiscount(new Discount(0.4));
        $item->setQty(3);
        $this->assertEquals(1200, $item->getSubtotal());
    }

    /**
     * @test
     */
    public function SectionClassTest()
    {
        $dataProvider = new DataProvider();
        $data = $dataProvider->getSectionData();
        $section = new Section();

        $this->runSetter($section, $data);
        $this->runDataTest($section, $data);
    }
}
